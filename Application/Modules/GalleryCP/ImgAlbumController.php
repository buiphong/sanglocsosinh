<?php
class ImgAlbumController extends Controller{
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	public function indexAction(){
		$this->tpl->assign("indexLink", $this->url->action("list"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
		$sortType = array('date' => 'Thời gian', 'orderno' => 'Vị trí');
		//Lấy danh sách album dưới dạng cây
		$model = new Models_ImageAlbum();
		$modelImg = new Models_Images();
		$albums = $model->getTreeAlbum(0, true);
		if (!empty($this->params['album_id']))
			$modelImg->db->where('album_id', $this->params['album_id']);
		if (!empty($this->params['sort'])) {
			if ($this->params['sort'] == 'date') {
				$modelImg->db->orderby('create_time', 'desc');
			}
			elseif ($this->params['sort'] == 'orderno')
				$modelImg->db->orderby('orderno');
		}
		$images = $modelImg->db->select('id,name,file_path')->getFieldsArray();
		
		foreach ($images as $image)
		{
			$image['file_path_thumb'] = $this->url->thumbnail($image['file_path'], 200, 110);
			$this->tpl->assign('editLink', $this->url->action('editPic', 'Images', array('id' => $image['id'])));
			$this->tpl->assign('deleteLink', $this->url->action('deletePic', 'Images', array('id' => $image['id'])));
			$this->tpl->insert_loop('main.image', 'image', $image);
		}
		$this->tpl->assign('cmbAlbum', $this->html->genSelect('album_id', $albums, @$this->params['album_id'], '', '', array('class' => 'chosen-select'), 'Toàn bộ'));
		$this->tpl->assign('cmbUploadAlbum', $this->html->genSelect('album_id', $albums, @$this->params['album_id'], '', '', array('class' => ''), 'Toàn bộ'));
		
		$this->tpl->assign('cmbSort', $this->html->genSelect('sort', $sortType, @$this->params['sort'], '', '', array('class' => 'chosen-select')));
		$this->tpl->assign('formUpload', $this->url->action('upload', 'Images'));
		$this->tpl->assign('formAction', $this->url->action('index'));
		$this->tpl->assign("rightHeader", $this->renderAction(array("headerNotifyIcon", "ComponentCP", "ControlPanel")));
		return $this->view();
	}
	function listAction(){
        //Danh mục album
        $this->tpl->assign('sidebarCategory', $this->html->renderAction('sidebarCategory', array('id' => @$this->params['catid'])));
		$this->tpl->assign("catid", @$this->params["catid"]);
		$this->tpl->assign("addLink", $this->url->action("add"));
		$this->tpl->assign("indexLink", $this->url->action("list"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
		$this->tpl->assign("rightHeader", $this->renderAction(array("headerNotifyIcon", "ComponentCP", "ControlPanel")));
		return $this->view();
	}
    public function dataTableAjax(){
        $model = new Models_ImageAlbum();
        $pageSize = @$this->params["iDisplayLength"];
        $offset = @$this->params["iDisplayStart"];

        if(isset($this->params["catid"]) && $this->params["catid"] != "")
            $model->db->where("category_id", $this->params["catid"]);
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
					$datas[$key]["status"] = "Đang sử dụng";
                else
					$datas[$key]["status"] = "Đang tạm ẩn";
                $href = $this->url->action("index", array('album_id' => $val['id']));
                $datas[$key]["name"] = "<a href='$href'>" . $val["name"] . "</a>";
            }
        }
        $data["iTotalDisplayRecords"] = $data["iTotalRecords"];
        $data["aaData"] = $datas;
        return json_encode($data);
    }
	function createAjax(){
		$this->setView("edit");
		$model = new Models_ImageAlbum();
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $model->getTreeAlbum(0, true), @$this->params["parent_id"]));
        //category
        $this->tpl->assign('category_id', $this->html->genSelect('category_id', Models_ImgAlbumCategory::getTreeCat(), @$this->params['catid']));
		$this->tpl->assign("form_action", $this->url->action("createPost"));
        $this->unloadLayout();
		return $this->view();
	}
	function createPostAjax(Models_ImageAlbum $model){
        $model->create_time = date("Y-m-d H:i:s", time());
        $model->created_uid = $_SESSION["vc_control_panel"]["system_userid"];
        $model->lang_code = @$_SESSION["sys_langcode"];
		if ($model->Insert())
            return json_encode(array("success" => true, "msg" => "Thêm mới thành công","dataTable"=>"tableImgAlbum"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function editAjax(){
		$key = @$this->params["id"];
		$model = new Models_ImageAlbum($key);
		if ($model->status)
			$this->tpl->assign("checked", "checked");
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $model->getTreeAlbum(), $model->parent_id));
        $this->tpl->assign('category_id', $this->html->genSelect('category_id', Models_ImgAlbumCategory::getTreeCat(), $model->category_id));
		$this->tpl->assign("form_action", $this->url->action("editPost"));
		$this->tpl->assign("homeCPLink", $this->url->action("index", "Index", "ControlPanel"));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	function editPostAjax(Models_ImageAlbum $model){
        $model->create_time = date("Y-m-d H:i:s", time());
		if ($model->Update())
            return json_encode(array("success" => true, "msg" => "Cập nhật thông tin thành công","dataTable"=>"tableImgAlbum"));
        else
            return json_encode(array("success" => false, "msg" => $model->db->error));
	}
	function deleteAjax(){
		$model = new Models_ImageAlbum();
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
        return json_encode(array("success" => true,"dataTable"=>"tableImgAlbum"));
	}

    /**
     * sidebar news category
     */
    public function sidebarCategoryAction()
    {
        $cats = Models_ImgAlbumCategory::getCatMultiLevel();
        if($cats)
            $this->tpl->assign('html', $this->html->renderAction('_childSidebarCat', array('cats' => $cats, 'selected' => @$this->params['id'])));
        $this->tpl->assign('listLink', $this->url->action('index'));
        $this->unloadLayout();
        return $this->view();
    }

    public function _childSidebarCatAction()
    {
        foreach ($this->params['cats'] as $cat)
        {
            if (!empty($cat['subs']))
            {
                $this->tpl->assign('child', $this->html->renderAction('_childSidebarCat',
                    array('cats' => $cat['subs'], 'selected' => $this->params['selected'])));
            }
            else
                $this->tpl->assign('child', '');
            if ($cat['id'] == $this->params['selected']) {
                $cat['class'] = 'current';
            }
            else
                $cat['class'] = '';
            $cat['link'] = $this->url->action('index', array('catid'=>$cat['id']));
            //$this->tpl->assign('child', $this->html->renderAction('_childSidebarNews', array('cats' =>)));
            $this->tpl->insert_loop('main.cat', 'cat', $cat);
        }
        $this->unloadLayout();
        return $this->view();
    }
}
?>