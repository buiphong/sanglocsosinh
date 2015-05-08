<?php
class VideosController extends Controller{
	private $arrStatus = array(0=>"Đã hạ xuống",1=>"Đang kích hoạt");
	public function __init(){
		$this->loadTemplate('Metronic');
		$this->checkPermission();
	}
	function indexAction(){
        $model = new Models_VideoAlbum();
		$listAlbum = $model->getArrayParent(@$this->params["album_id"]);
		foreach ($listAlbum as $album){
			$this->tpl->assign('albumLink', $this->url->action('index', array('album_id' => $album['id'])));
			$this->tpl->insert_loop('main.album', 'album', $album);
		}
		if(isset($this->params["album_id"]) && !empty($this->params["album_id"]))
			$nameCurrent = $model->db->select('name')->where('id', @$this->params["album_id"])->getField();
		else
			$nameCurrent = "Danh sách";
		$this->tpl->assign("nameCurrent", $nameCurrent);
		$this->tpl->assign("album_id", @$this->params["album_id"]);
		$this->tpl->assign("addLink", $this->url->action("add"));
		$this->tpl->assign("indexLink", $this->url->action("index"));
		$this->tpl->assign("indexVideoLink", $this->url->action("index", "VideoAlbum"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
		$this->tpl->assign("rightHeader", $this->renderAction(array("headerNotifyIcon", "ComponentCP", "ControlPanel")));
		return $this->view();
	}
    public function dataTableAjax(){
        $model = new Models_Videos();
        $pageSize = @$this->params["iDisplayLength"];
        $offset = @$this->params["iDisplayStart"];
        if(isset($this->params["album_id"]) && $this->params["album_id"] != "")
            $model->db->where("album_id", $this->params["album_id"]);
        if(isset($this->params["sSearch"]))
            $model->db->like("name", $this->params["sSearch"]);
        if(isset( $_GET["iSortCol_0"] ) ){
            if ( $_GET[ "bSortable_".intval($_GET["iSortCol_0"]) ] == "true")
                $model->db->orderby($_GET["mDataProp_".$_GET["iSortCol_0"]] ,$_GET["sSortDir_0"]==="asc" ? "asc" : "desc");
        }
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array("sEcho" => @$this->params["sEcho"], "iTotalRecords" => $totalRow);
        $datas = $model->db->limit($pageSize, $offset)->getFieldsArray();
        if(!empty($datas)){
            foreach($datas as $key => $val){
                $datas[$key]["create_time"] = date("d-m-Y", strtotime($val["create_time"]));
				if($val["status"])
					$datas[$key]["status"] = "Đã kích hoạt";
                else
					$datas[$key]["status"] = "Ngừng kích hoạt";
            }
        }
        $data["iTotalDisplayRecords"] = $data["iTotalRecords"];
        $data["aaData"] = $datas;
        return json_encode($data);
    }
	function createAjax(){
		$this->setView("edit");
		$model = new Models_VideoAlbum();
		$this->tpl->assign('album_id', $this->html->genSelect('album_id', $model->getTreeAlbum(0, true), @$this->params["album_id"]));
		$this->tpl->assign("form_action", $this->url->action("createPost"));
        $this->unloadLayout();
		return $this->view();
	}
	function createPostAjax(Models_Videos $model){
        $model->create_time = date("Y-m-d H:i:s", time());
        $model->created_uid = $_SESSION["pt_control_panel"]["system_userid"];
        $model->lang_code = @$_SESSION["sys_langcode"];
		if ($model->Insert())
            return json_encode(array("success" => true, "msg" => "Thêm mới thành công","dataTable"=>"tableVideos"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function editAjax(){
		$key = @$this->params["id"];
		$modelAlbum = new Models_VideoAlbum($key);
		$model = new Models_Videos($key);
		if ($model->status)
			$this->tpl->assign("checked", "checked");
		$this->tpl->assign('album_id', $this->html->genSelect('album_id', $modelAlbum->getTreeAlbum(0, true), $model->album_id));
		$this->tpl->assign("form_action", $this->url->action("editPost"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	function editPostAjax(Models_Videos $model){
        $model->create_time = date("Y-m-d H:i:s", time());
		if ($model->Update())
            return json_encode(array("success" => true, "msg" => "Cập nhật thông tin thành công","dataTable"=>"tableVideos"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function deleteAjax(){
		$model = new Models_Videos();
		$ids = $this->params["id"];
		if (strpos($ids, ",") !== false){
			$ids = explode(",", $ids);
			foreach ($ids as $id)
				if ($id != "")
				    if(!$model->Delete("id='$id'"))
					    return json_encode(array("success" => false, "msg" => $model->error));
		}
		elseif ($ids != ""){
			if(!$model->Delete("id='$ids'"))
				return json_encode(array("success" => false, "msg" => $model->error));
		}
        return json_encode(array("success" => true,"dataTable"=>"tableVideos"));
	}
}
?>