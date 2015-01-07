<?php
class VideoAlbumController extends Controller{
	public function __init(){
		$this->loadTemplate('Metronic');
		$this->checkPermission();
	}
	function indexAction(){
        $model = new Models_VideoAlbum();
		$listAlbum = $model->getArrayParent(@$this->params["parent_id"]);
		foreach ($listAlbum as $album){
			$this->tpl->assign('albumLink', $this->url->action('index', array('parent_id' => $album['id'])));
			$this->tpl->insert_loop('main.album', 'album', $album);
		}
		if(isset($this->params["parent_id"]) && !empty($this->params['parent_id']))
			$nameCurrent = $model->db->select('name')->where('id', @$this->params["parent_id"])->getField();
		else
			$nameCurrent = "Danh sách";
		$this->tpl->assign("nameCurrent", $nameCurrent);
		$this->tpl->assign("parent_id", @$this->params["parent_id"]);
		$this->tpl->assign("addLink", $this->url->action("add"));
		$this->tpl->assign("indexLink", $this->url->action("index"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
		$this->tpl->assign("rightHeader", $this->renderAction(array("headerNotifyIcon", "ComponentCP", "ControlPanel")));
		return $this->view();
	}
    public function dataTableAjax(){
        $model = new Models_VideoAlbum();
        $pageSize = @$this->params["iDisplayLength"];
        $offset = @$this->params["iDisplayStart"];
        if(isset($this->params["parent_id"]) && $this->params["parent_id"] != "")
            $model->db->where("parent_id", $this->params["parent_id"]);
		else
            $model->db->where("parent_id", 0);
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
                $href = $this->url->action("index", array('parent_id' => $val['id']));
                $datas[$key]["name"] = "<a href='$href'>" . $val["name"] . "</a>";
            }
        }
        $data["iTotalDisplayRecords"] = $data["iTotalRecords"];
        $data["aaData"] = $datas;
        return json_encode($data);
    }
	function createAjax(){
		$this->setView("edit");
		$model = new Models_VideoAlbum();
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $model->getTreeAlbum(0, true), @$this->params["parent_id"]));
		$this->tpl->assign("form_action", $this->url->action("createPost"));
        $this->unloadLayout();
		return $this->view();
	}
	function createPostAjax(Models_VideoAlbum $model){
        $model->create_time = date("Y-m-d H:i:s", time());
        $model->created_uid = $_SESSION["vc_control_panel"]["system_userid"];
        $model->lang_code = @$_SESSION["sys_langcode"];
		if ($model->Insert())
            return json_encode(array("success" => true, "msg" => "Thêm mới thành công","dataTable"=>"tableVideoAlbum"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function editAjax(){
		$key = @$this->params["id"];
		$model = new Models_VideoAlbum($key);
		if ($model->status)
			$this->tpl->assign("checked", "checked");
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $model->getTreeAlbum(0, true, false, $model->id), $model->parent_id));
		$this->tpl->assign("form_action", $this->url->action("editPost"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	function editPostAjax(Models_VideoAlbum $model){
        $model->create_time = date("Y-m-d H:i:s", time());
		if ($model->Update())
            return json_encode(array("success" => true, "msg" => "Cập nhật thông tin thành công","dataTable"=>"tableVideoAlbum"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function deleteAjax(){
		$model = new Models_VideoAlbum();
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
        return json_encode(array("success" => true,"dataTable"=>"tableVideoAlbum"));
	}
}
?>