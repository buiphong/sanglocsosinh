<?php
class ConfigGroupCPController extends Controller
{
	public function __init()
	{
		$this->loadTemplate('Metronic');
		$this->checkPermission();
	}
	
	public function indexAction()
	{
		$pageSize = 20;
		
		$page = @$this->params['page'];
		if (empty($page))
			$page = 1;
		
		$offset = ($page - 1) * $pageSize;
		
		$model = new Models_ConfigGroup();
		
		$totalRows = $model->db->count();
		
		$groups = $model->db->select('id,name,desc')->orderby('name')->limit($pageSize, $offset)->getAll();
		if(!empty($groups))
		{
			foreach ($groups as $group)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $group['id'])));
				$this->tpl->assign('itemLink', $this->url->action('index', 'ConfigCP', array('groupid' => $group['id'])));
				$this->tpl->insert_loop('main.group', 'group', $group);
			}
		}
		
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('groupConfigLink', $this->url->action('index'));
        $this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}
	
	/**
	 * Thêm mới slide
	 */
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('form_action', $this->url->action('create'));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view();
	}
	
	public function createPost(Models_ConfigGroup $model)
	{
		if($model->Insert())
			$this->url->redirectAction('index');
		else
			$this->showError('Mysql Error', $model->error);
	}
	
	/**
	 * Sửa thông tin danh mục
	 */
	public function editAction()
	{
		$model = new Models_ConfigGroup($this->params['id']);
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view($model);
	}
	
	public function editPost(Models_ConfigGroup $model)
	{
		if($model->Update())
			$this->url->redirectAction('index');
		else
			$this->showError('Mysql Error', $model->error);
	}
	
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$model = new Models_ConfigGroup();
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
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
}