<?php
class CategoryDocumentsCPController extends Controller
{
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Danh mục tin bài
	 */
	public function indexAction(){
		$model = new Models_DocumentsCategory();
		//Lấy theo từng cấp
		if (!empty($this->params['parentid'])){
			$parentid = $this->params['parentid'];
			$parentName = $model->db->select('title')->where('id', $parentid)->getField();
            $this->tpl->assign('parentId', $this->params['parentid']);
		}
		else{
			$parentid = 0;
			$parentName = 'Danh mục cha';
		}
		$this->tpl->assign('createLink', $this->url->action('create', array('parentid' => @$this->params['parentid'])));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('parentName', $parentName);
		$this->tpl->assign('breadCrumb', $this->html->renderAction('getBreadCrumb', array('parentid' => $parentid)));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
		return $this->view();
	}

    /**
     * Danh sách loại tài liệu
     */
    public function listAjax()
    {
        $model = new Models_DocumentsCategory();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        //Lấy theo từng cấp
        if (!empty($this->params['parentid']))
        {
            $parentid = $this->params['parentid'];
            $model->db->where('parent_id', $parentid);
        }

        if(!empty($this->params['sSearch']))
            $model->db->like('title', $this->params['sSearch']);

        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('orderno');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('id,title,orderno')
            ->limit($pageSize,$offset)->getFieldsArray();
        if(!empty($datas))
        {
            foreach($datas as $key => $val)
            {
                $datas[$key]['title'] = "<a href='".$this->url->action('index', array('parentid' => $val['id']))."'>".$val['title']."</a>";
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
	
	/**
	 * Thêm mới danh mục
	 */
	public function createAjax(){
		$this->setView('edit');
		$model = new Models_DocumentsCategory();
		//Lấy danh sách danh mục để làm danh mục cha
		$arrCat = $model->getTreeCategory(0, true, @$_SESSION['sys_langcode']);
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $arrCat, @$this->params['parentid']));
		$this->tpl->assign('listLink', $this->url->action('index', array('parentid' => @$this->params['parentid'])));
		$this->tpl->assign('catName', 'Thêm mới');
        $this->tpl->assign('form_action', $this->url->action('createPost'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        $this->unloadLayout();
		return $this->view();
	}
	
	public function createPostAjax(Models_DocumentsCategory $model){
		$model->lang_code = @$_SESSION['sys_langcode'];
		if($model->Insert())
            return json_encode(array('success' => true, 'dataTable' => 'tableDocumentCat'));
        else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	
	/**
	 * Sửa thông tin danh mục
	 */
	public function editAjax(){
		$model = new Models_DocumentsCategory($this->params['id']);
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $model->getTreeCategory(0, true, @$_SESSION['sys_langcode']), $model->parent_id));
		$this->tpl->assign('listLink', $this->url->action('index', array('parentid' => $model->parent_id)));
		if ($model->has_rss)
			$this->tpl->assign('checked', 'checked');
		else
			$this->tpl->assign('checked', '');
		
		if ($model->is_member)
			$this->tpl->assign('isMember', 'checked');
		else
			$this->tpl->assign('isMember', '');
		
		$this->tpl->assign('catName', $model->title);
        $this->tpl->assign('form_action', $this->url->action('editPost'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	public function editPostAjax(Models_DocumentsCategory $model){
		$model->lang_code = @$_SESSION['sys_langcode'];
		if (empty($model->is_member)) {
			$model->is_member = 0;
		}
		if($model->Update())
			return json_encode(array('success' => true, 'dataTable' => 'tableDocumentCat'));
		else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	
	function deleteAjax(){
		$model = new Models_DocumentsCategory();
		$ids = $this->params['id'];
		if (strpos($ids, ',') !== false){
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->Delete("id=$id")){
					return json_encode(array('success' => false, 'msg' => $model->error));
					break;
				}
		}
		elseif ($ids != ''){
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $$model->error));
		}
		return json_encode(array('success' => true, 'dataTable' => 'tableDocumentCat'));
	}
	
	/**
	 * Lấy đường dẫn vị trí
	 */
	public function getBreadCrumbAction(){
		$model = new Models_DocumentsCategory();
		$this->unloadLayout();
		if (!empty($this->params['parentid'])) {
			//Check parent
			$arrParent = $model->getArrayParent($this->params['parentid']);
			foreach ($arrParent as $cat){
				$this->tpl->assign('catLink', $this->url->action('index', array('parentid' => $cat['id'])));
				$this->tpl->insert_loop('main.cat', 'cat', $cat);
			}
		}
		return $this->view();
	}
}