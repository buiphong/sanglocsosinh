<?php
class CounterController extends Presentation
{
    public function __init()
    {
        $this->loadModule('CounterCP');
    }

    public function boxCounterAction()
    {
        if(!isset($_SESSION['hasCounter']))
            Models_CounterDetail::updateCounter();

        //Lấy thông tin thống kê
        $this->tpl->assign('totalVisit', Models_WebsiteData::getDataValue('visit'));
        //Lấy thông tin thống kê theo ngày
        $this->tpl->assign('visitDate', Models_CounterDetail::getCounterByDate());
        //Thống kê lượng truy cập hiện tại
        $this->tpl->assign('currVisit', 1);
        $this->tpl->assign('sessionId', session_id());
        $this->tpl->assign('ipAddress', $_SERVER['REMOTE_ADDR']);
        return $this->view();
    }

    public function OnlineAjax() {
        $folder = 'counter';
        if(!is_dir($folder)) mkdir($folder, 0777);   // Tạo thư mục counter nếu chưa có
        $params = $this->params;
        //Tạo file khi có thành viên mới
        //$visiter = md5($params['user']);//mã hóa filename
        $filename = $folder.'/'.$params['ipAddress'].'_'.$params['sessionId'].'.onl';
        $f = fopen($filename, "w");
        fclose($f);
        $online = array();
        $online['total'] = 0;
        $current_time = time();
        $timeout = 60;//60 = 1 minute => đếm trong vòng 5s

        //Load danh sách các file đã được tạo
        $flist = scandir($folder);
        foreach($flist as $num => $file) {
            $filer = $folder.'/'.$file;
            if(substr($filer, strrpos($filer, '.') + 1) == 'onl')
            {
                $ftime = filemtime($filer);
                if ($current_time - $ftime > $timeout)   unlink($filer);// Xóa file
                else
                {
                    $online[] = preg_replace("#\.onl#","",$file);
                    $online['total']++;
                }
            }
        }
        return json_encode(array("online" => $online));
    }
}