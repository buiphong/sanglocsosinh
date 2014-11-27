<?php
class SupportGroupCPController extends Controller
{
	public function __init()
	{
		$this->loadTemplate('flatadmin');
	}
	
	public function indexAction()
	{
		$pageSize = 20;
		
		$page = @$this->params['page'];
		if (empty($page))
			$page = 1;
		
		$offset = ($page - 1) * $pageSize;
		$model = new Models_SupportGroup();
		if(MULTI_LANGUAGE)
		    $model->db->where('lang_code', $_SESSION['sys_langcode']);
		
		$totalRows = $model->db->count();
		$groups = $model->db->limit($pageSize, $offset)->getcFieldsArray();
		if(!empty($groups))
		{
			foreach ($groups as $group)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $group['id'])));
				$this->tpl->assign('childLink', $this->url->action('index', 'OnlineSupportCP', array('group_id' => $group['id'])));
				$this->tpl->insert_loop('main.group', 'group', $group);
			}
		}
		
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index'));
		return $this->view();
	}
	
	public function indexPost()
	{
		$this->url->redirectAction('index', $this->params);
	}
	
	/**
	 * Thêm mới group
	 */
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('groupName', 'Thêm mới');
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view();
	}
	
	public function createPost(Models_SupportGroup $model)
	{
        if(MULTI_LANGUAGE)
		    $model->lang_code = $_SESSION['sys_langcode'];
		$model->status = 1;
		$model->create_time = date('Y-m-d H:i:s');
		if($model->Insert())
			$this->url->redirectAction('index');
		else
			$this->showError('Mysql Error', $model->db->error);
	}
	
	public function editAction()
	{
		$model = new Models_SupportGroup($this->params['id']);
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('catName', $model->name);
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view($model);
	}
	
	public function editPost(Models_SupportGroup $model)
	{
        if(MULTI_LANGUAGE)
		    $model->lang_code = $_SESSION['sys_langcode'];
		if($model->Update())
			$this->url->redirectAction('index', array('parentid' => $model->parent_id));
		else
			$this->showError('Mysql Error', $model->db->error);
	}
	
	function deleteAjax()
	{
		$model = new Models_SupportGroup();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
			if ($id != '')
			if(!$model->Delete("id=$id"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $$model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
}