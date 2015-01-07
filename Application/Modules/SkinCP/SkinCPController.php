<?php
class SkinCPController extends Controller{
	
	public $skinDir;
    public $skinExt = 'htm';
	
	function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
		//Check skin dir
		$this->skinDir = Url::getAppDir().'Skins';
		if (!is_dir($this->skinDir))
			$this->showError('System error', 'Không tìm thấy thư mục skin tại: ' . $this->skinDir);
	}
	
	/**
	 * Danh sách skin
	 */
	public function listAction(){	
		$listSkins = $this->getSkin();
		foreach($listSkins as $key=>$listSkin){
			$temp = array();
			$temp['name'] = $key;
			$temp['file'] = $listSkin;
			$this->tpl->assign('editLink', $this->url->action('edit', array('key' => $key)));
			$this->tpl->insert_loop('main.skin', 'skin', $temp);
		}
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->parse('main.button');
		return $this->view();
	}
	/**
	 * Thêm mới skin
	 */
	public function createAction(){
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('paramType', $this->html->genSelect('type', $this->type));
		$this->tpl->assign('portlet_id', @$this->params['portletid']);
		return $this->view();
	}
	
	public function createPost(){
		$filename = $this->skinDir . DIRECTORY_SEPARATOR . $this->params['name'].'.' . $this->skinExt;
		if (file_exists($filename))
			die("File " . $filename . " đã tồn tại");
			
		$string = $this->params['content'];

		$fp = fopen($filename, 'w');
		fwrite($fp, $string);
		fclose($fp);
		$this->url->redirectAction('list');
	}
	
	/**
	 * Sửa skin
	 */
	public function editAction(){
		$fileUrl = $this->skinDir . DIRECTORY_SEPARATOR.$this->params['key'] . '.' . $this->skinExt;
		if (!file_exists($fileUrl))
			die("File " . $fileUrl . " không tồn tại");;
		$file = file_get_contents($fileUrl, true);
		
		$this->tpl->assign('listLink', $this->url->action('list'));

		$this->tpl->assign('content', htmlspecialchars($file));
		$this->tpl->assign('name', $this->params['key']);
		return $this->view();
	}
	public function editPost(){
        echo var_dump($this->params); die;
		$filename = $this->skinDir . DIRECTORY_SEPARATOR.$this->params['name'].'.' . $this->skinExt;
		
		if (!file_exists($filename))
			$this->showError('VCMS Error', "File " . $filename . " không tồn tại");;
			
		$string = $this->params['content'];

		$fp = fopen($filename, 'w');
		fwrite($fp, $string);
		fclose($fp);
	}
	public function deleteAjax(){
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false){
			$ids = explode(',', $ids);
			foreach ($ids as $id){
				if ($id != '')
                {
                    $filename = $this->skinDir .DIRECTORY_SEPARATOR.$id . '.' . $this->skinExt;
					if(!unlink($filename))
						return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
                }
			}
		}
		elseif ($ids != ''){
			if ($ids != '')
            {
                $filename = $this->skinDir . DIRECTORY_SEPARATOR.$ids . '.' . $this->skinExt;
				if(!unlink($filename))
					return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
            }
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('list')));
	}
	
	function getSkin(){
		$list = scandir($this->skinDir);
		$ignoredItem = array('.', '..','.svn');
		$arrItem = array();
		foreach ($list as $item)
		{
			if (!(array_search($item, $ignoredItem) > -1))
			{
				$fileName = substr($item, 0, -4);
				$arrItem[$fileName] = $item;
			}
		}
		return $arrItem;
	}
}