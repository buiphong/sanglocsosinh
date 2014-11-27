<?php
class BionetContactCPController extends Controller
{
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Danh mục tin bài
	 */
	public function indexAction(){
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index'));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        $this->setTitle('Quản lý điểm thu mẫu');
		return $this->view();
	}

    /**
     * Danh sách loại tài liệu
     */
    public function listAjax()
    {
        $model = new Models_BionetContact();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        if(!empty($this->params['sSearch']))
            $model->db->like('title', $this->params['sSearch']);

        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('order_no');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('id,title,order_no,status')
            ->limit($pageSize,$offset)->getFieldsArray();

        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
	
	/**
	 * Thêm mới danh mục
	 */
	public function createAjax(){
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('catName', 'Thêm mới');
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('form_action', $this->url->action('createPost'));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        $this->unloadLayout();
		return $this->view();
	}
	
	public function createPostAjax(Models_BionetContact $model){
		if($model->Insert())
            return json_encode(array('success' => true, 'dataTable' => 'tableBionetContact'));
        else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	
	/**
	 * Sửa thông tin danh mục
	 */
	public function editAjax(){
		$model = new Models_BionetContact($this->params['id']);
		$this->tpl->assign('listLink', $this->url->action('index'));
		if ($model->status == 1)
			$this->tpl->assign('checked', 'checked');
		else
			$this->tpl->assign('checked', '');
		
		$this->tpl->assign('catName', $model->title);
        $this->tpl->assign('form_action', $this->url->action('editPost'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	public function editPostAjax(Models_BionetContact $model){
		if (empty($model->status)) {
			$model->status = 0;
		}
		if($model->Update())
			return json_encode(array('success' => true, 'dataTable' => 'tableBionetContact'));
		else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	
	function deleteAjax(){
		$model = new Models_BionetContact();
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
		return json_encode(array('success' => true, 'dataTable' => 'tableBionetContact'));
	}
}