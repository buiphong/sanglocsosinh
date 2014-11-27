<?php
class SliderTypeCPController extends Controller{
	public function __init(){
		$this->loadTemplate('Metronic');
		$this->checkPermission();
	}
	function indexAction(){
		$this->tpl->assign("addLink", $this->url->action("add"));
		$this->tpl->assign("indexLink", $this->url->action("index"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
		$this->tpl->assign("rightHeader", $this->renderAction(array("headerNotifyIcon", "ComponentCP", "ControlPanel")));
		return $this->view();
	}
    public function dataTableAjax(){
        $model = new Models_SliderType();
        $pageSize = @$this->params["iDisplayLength"];
        $offset = @$this->params["iDisplayStart"];

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
                $href = $this->url->action("index", "SliderCP", array('type_id' => $val['id']));
                $datas[$key]["name"] = "<a href='$href'>" . $val["name"] . "</a>";
            }
        }
        $data["iTotalDisplayRecords"] = $data["iTotalRecords"];
        $data["aaData"] = $datas;
        return json_encode($data);
    }
	function createAjax(){
		$this->setView("edit");
		$model = new Models_SliderType();
		$this->tpl->assign("form_action", $this->url->action("createPost"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
        $this->unloadLayout();
		return $this->view();
	}
	function createPostAjax(Models_SliderType $model){
        $model->create_time = date("Y-m-d H:i:s", time());
        $model->create_uid = $_SESSION["vc_control_panel"]["system_userid"];
        $model->lang_code = @$_SESSION["sys_langcode"];
		if ($model->Insert())
            return json_encode(array("success" => true, "msg" => "Thêm mới thành công","dataTable"=>"tableSliderType"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function editAjax(){
		$key = @$this->params["id"];
		$model = new Models_SliderType($key);
		if ($model->status)
			$this->tpl->assign("checked", "checked");
		if ($model->is_default)
			$this->tpl->assign("default_checked", "checked");
		$this->tpl->assign("form_action", $this->url->action("editPost"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	function editPostAjax(Models_SliderType $model){
		if ($model->Update())
            return json_encode(array("success" => true, "msg" => "Cập nhật thông tin thành công","dataTable"=>"tableSliderType"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function deleteAjax(){
		$model = new Models_SliderType();
		$ids = $this->params["id"];
		if (strpos($ids, ",") !== false){
			$ids = explode(",", $ids);
			foreach ($ids as $id)
				if ($id != "")
				    if(!$model->Delete("id='$id'"))
                    {
                        Models_Slider::delSliderByType($id);
                        return json_encode(array("success" => false, "msg" => $model->error));
                    }
		}
		elseif ($ids != ""){
			if(!$model->Delete("id='$ids'"))
            {
                Models_Slider::delSliderByType($ids);
                return json_encode(array("success" => false, "msg" => $model->error));
            }
		}
        return json_encode(array("success" => true,"dataTable"=>"tableSliderType"));
	}
}
?>