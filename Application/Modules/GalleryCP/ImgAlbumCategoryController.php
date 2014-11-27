<?php
class ImgAlbumCategoryController extends Controller{
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}

	function indexAction(){
        $model = new Models_ImgAlbumCategory();
		if(@$this->params["parent_id"] > 0)
			$nameCurrent = $model->db->select('name')->where('id', @$this->params["parent_id"])->getField();
		else
			$nameCurrent = "Danh sách";
		$this->tpl->assign("nameCurrent", $nameCurrent);
		$this->tpl->assign("parent_id", @$this->params["parent_id"]);
		$this->tpl->assign("addLink", $this->url->action("add"));
		$this->tpl->assign("indexLink", $this->url->action("list"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
		$this->tpl->assign("rightHeader", $this->renderAction(array("headerNotifyIcon", "ComponentCP", "ControlPanel")));
		return $this->view();
	}
    public function dataTableAjax(){
        $model = new Models_ImgAlbumCategory();
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
                $href = $this->url->action("list", array('parent_id' => $val['id']));
                $datas[$key]["name"] = "<a href='$href'>" . $val["name"] . "</a>";
            }
        }
        $data["iTotalDisplayRecords"] = $data["iTotalRecords"];
        $data["aaData"] = $datas;
        return json_encode($data);
    }
	function createAjax(){
		$this->setView("edit");
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', Models_ImgAlbumCategory::getTreeCat(0, true), @$this->params["parent_id"]));
		$this->tpl->assign("form_action", $this->url->action("createPost"));
        $max = Models_ImgAlbumCategory::getMaxOrderNo(@$this->params['parent_id']);
        $max += 1;
        $this->tpl->assign('order_no', $this->getCbmOrder($max, $max));
        $this->tpl->assign('form_action', $this->url->action('createPost'));
        $this->unloadLayout();
		return $this->view();
	}
	function createPostAjax(Models_ImgAlbumCategory $model){
        $model->lang_code = @$_SESSION["sys_langcode"];
		if ($model->Insert())
            return json_encode(array("success" => true, "msg" => "Thêm mới thành công","dataTable"=>"tableImgAlbumCat"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function editAjax(){
		$key = @$this->params["id"];
		$model = new Models_ImgAlbumCategory($key);
		if ($model->status)
			$this->tpl->assign("checked", "checked");
        $max = Models_ImgAlbumCategory::getMaxOrderNo($model->parent_id);
        $this->tpl->assign('order_no', $this->getCbmOrder($max, $model->order_no));
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', Models_ImgAlbumCategory::getTreeCat(), $model->parent_id));
		$this->tpl->assign("form_action", $this->url->action("editPost"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	function editPostAjax(Models_ImgAlbumCategory $model){
        //Check for order_no
        if($model->order_no != $this->params['old_orderno'])
            $model->db->where('order_no', $model->order_no)->where('parent_id', $model->parent_id)
                ->update(array('order_no' => $this->params['old_orderno']));
		if ($model->Update())
            return json_encode(array("success" => true, "msg" => "Cập nhật thông tin thành công","dataTable"=>"tableImgAlbumCat"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function deleteAjax(){
		$model = new Models_ImgAlbumCategory();
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
        return json_encode(array("success" => true,"dataTable"=>"tableImgAlbumCat"));
	}

    function getCbmOrder($total, $selected = 0, $plus = 0)
    {
        $arr = array();
        if($total >= 1)
            for($i = 1; $i <= $total; $i++)
                $arr[$i] = $i;
        return $this->html->genSelect('order_no', $arr, $selected);
    }
}
?>