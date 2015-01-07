<?php
class SpecialTypeCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Danh mục tin bài
	 */
	public function indexAction()
	{
		$pageSize = 20;
	
		$page = @$this->params['page'];
		if (empty($page))
			$page = 1;
	
		$offset = ($page - 1) * $pageSize;
	
		$modelSpecialType = new Models_NewsSpecialType();
        if(!empty($_SESSION['sys_langcode']))
		    $modelSpecialType->db->where('lang_code', $_SESSION['sys_langcode']);
		$totalRows = $modelSpecialType->db->count();
		$types = $modelSpecialType->db->select('id, code, title')->orderby('code','asc')->limit($pageSize, $offset)->getAll();

		if(!empty($types))
		{
			foreach ($types as $type)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $type['id'])));
				$this->tpl->assign('childLink', $this->url->action('index'));
				$this->tpl->insert_loop('main.type', 'type', $type);
			}
		}
	
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index', 'SpecialNewsCP'));
		$this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}


    public function listAjax()
    {
        $modelSpecialType = new Models_NewsSpecialType();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];


        if(!empty($this->params['sSearch']))
            $modelSpecialType->db->like('title', $this->params['sSearch']);

        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $modelSpecialType->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $modelSpecialType->db->orderby('title', 'asc');
        $totalRow = $modelSpecialType->db->count()?$modelSpecialType->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $modelSpecialType->db->select('id,title,code')
            ->limit($pageSize,$offset)->getFieldsArray();
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

	/**
	 * Thêm mới danh mục
	 */
	public function createAjax()
	{
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index', 'SpecialNewsCP'));
		$this->tpl->assign('listTypeLink', $this->url->action('index'));
		$this->tpl->assign('typeName', 'Thêm mới');
        $this->tpl->assign('form_action', $this->url->action('createPost'));
        $this->unloadLayout();
		return $this->view();
	}
	
	public function createPostAjax(Models_NewsSpecialType $model)
	{
		$model->lang_code = @$_SESSION['sys_langcode'];
		if($this->model->insert())
		{
            return json_encode(array('success' => true, 'dataTable' => 'tableNewsSType',
                'msg' => 'Thêm mới thành công'));
		}
		else
            return json_encode(array('success' => false, 'msg' => $this->model->error));
	}	
	/**
	 * Sửa thông tin danh mục
	 */
	public function editAjax()
	{
		$model =new Models_NewsSpecialType($this->params['id']);
		$this->tpl->assign('form_action', $this->url->action('editPost'));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index', 'SpecialNewsCP'));
		$this->tpl->assign('listTypeLink', $this->url->action('index'));
		$this->tpl->assign('typeName', $model->title);
        $this->unloadLayout();
		return $this->view($model);
	}
	
	public function editPostAjax(Models_NewsSpecialType $model)
	{	
		$model->lang_code = @$_SESSION['sys_langcode'];
		if($this->model->Update())
		{
            return json_encode(array('success' => true, 'dataTable' => 'tableNewsSType',
                'msg' => 'Cập nhật thành công'));
		}
		else
            return json_encode(array('success' => false, 'msg' => $this->model->error));
	}
	
	/**
	 * Xóa tin bài
	 */
	function deleteAjax()
	{
		$model = new Models_NewsSpecialType();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
					if(!$model->Delete("id=$id"))
						return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
}