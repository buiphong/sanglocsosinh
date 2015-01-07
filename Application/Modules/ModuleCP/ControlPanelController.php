<?php
/**
 * Manage your module
 */
class ControlPanelController extends Controller
{
    /**
     * Init
     */
    public function __init()
    {
        $this->checkPermission();
        $this->loadTemplate('flatadmin');
    }

    /**
     * get list module
     */
    public function listAction()
    {
        return $this->view();
    }

    public function listAjax()
    {
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];
        $modules = VccDirectory::getSubDirectories(
            APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Modules');
        $data = array();
        if(!empty($modules))
        {
            $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => count($modules));
            $datas = array();
            $i = 0;
            foreach($modules as $name => $path)
            {
                //get infomation module (version, new update)
                $datas[$i]['name'] = $name;
                $datas[$i]['version'] = Version_Helper::getVersion($name);
                //get lastest
                $lastest = Version_Helper::getLatest($name);
                $datas[$i]['update'] = 'Lastest';
                if($lastest)
                {
                    $datas[$i]['update'] = $lastest->version;
                }
                $datas[$i]['desc'] = 'Mô tả';
                $datas[$i]['btn'] = '<a class="btn-update-module" accesskey="'.$name.'">Update</a>';
                $i++;
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

    public function updateModuleAjax()
    {
        //Check for update
        if($this->params['module'])
        {
            $lastest = Version_Helper::getLatest($this->params['module']);
            $curr = Version_Helper::getVersion($this->params['module']);
            if($lastest->version > $curr)
            {
                $file = file_get_contents($lastest->downloadLink);
                //save file
                $fileName = ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp'. DIRECTORY_SEPARATOR.
                    $this->params['module'] . '_' . $lastest->version . '.zip';
                file_put_contents($fileName, $file);
                //update module
                $zip = new ZipArchive();
                $res = $zip->open($fileName);
                if ($res === TRUE) {
                    $updateDb = false;
                    if(Version_Helper::getVersionConfig($this->params['module'] . ':dbVersion') < $lastest->dbVersion)
                        $updateDb = true;
                    //Move old module to trash
                    rename(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' .
                        DIRECTORY_SEPARATOR . $this->params['module'], ROOT_PATH . DIRECTORY_SEPARATOR .'Trash' .
                        DIRECTORY_SEPARATOR . $this->params['module']);
                    //extract zip file
                    $zip->extractTo(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules');
                    $zip->close();
                    //delete downloaded file
                    unlink($fileName);
                    //Update database
                    if($updateDb)
                    {
                        $updateDb = Version_Helper::getUpdateConfig($this->params['module'] . ':updateDb');
                        if(is_array($updateDb))
                        {
                            foreach($updateDb as $query)
                            {
                                if(Db_Model::runSQL($query) !== true)
                                    return json_encode(array('success' => false, 'msg' => 'Lỗi update Database'));
                            }
                        }
                    }
                }
                return json_encode(array('success' => true));
            }
            else
                return json_encode(array('success' => false, 'msg' => 'Không có phiên bản update nào khả dụng'));
        }
    }
}