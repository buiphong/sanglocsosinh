<?php
class OnlineSupportCPController extends Controller
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
		$model = new Models_OnlineSupport();
	    if(MULTI_LANGUAGE)
		    $model->db->where('lang_code', $_SESSION['sys_langcode']);
		
		//Get by group
		if (!empty($this->params['group_id'])) {
			$model->db->where('group_id', $this->params['group_id']);
		}
	
		$totalRows = $model->db->count();
	
		$supports = $model->db->limit($pageSize, $offset)->getAll();
			
		if(!empty($supports))
		{
			foreach ($supports as $support)
			{
				$arrName = unserialize( $support['contact_ids']);
				$support['yahoo'] = $arrName['yahoo_id'];
				$support['skype'] = $arrName['skype_id'];
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $support['id'])));
				$this->tpl->insert_loop('main.support', 'support', $support);
			}
		}
	
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create', array('group_id' => @$this->params['group_id'])));
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
		$model = new Models_OnlineSupport();
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('groupName', 'Thêm mới');
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view();
	}
	
	public function createPost(Models_OnlineSupport $model)
	{
		//$model->lang_code = $_SESSION['sys_langcode'];
		$model->status = 1;
		$model->create_time = date('Y-m-d H:i:s');
		$support['skype_id'] = $this->params['skype_id'];
		$support['yahoo_id'] = $this->params['yahoo_id'];
		$model->contact_ids = serialize($support);
		if($model->Insert())
			$this->url->redirectAction('index');
		else
			$this->showError('Mysql Error', $model->db->error);
	}
	
	public function editAction()
	{
		$model = new Models_OnlineSupport($this->params['id']);
		$support = unserialize($model->contact_ids);
		$this->tpl->assign('support',$support);
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('catName', $model->fullname);
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view($model);
	}
	
	public function editPost(Models_OnlineSupport $model)
	{
        if(MULTI_LANGUAGE)
		    $model->lang_code = $_SESSION['sys_langcode'];
		$support['skype_id'] = $this->params['skype_id'];
		$support['yahoo_id'] = $this->params['yahoo_id'];
		$model->contact_ids = serialize($support);
		
		if($model->Update())
			$this->url->redirectAction('index', array('group_id' => $model->group_id));
		else
			$this->showError('Mysql Error', $model->db->error);
	}
	
	function deleteAjax()
	{
		$model = new Models_OnlineSupport();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
			if ($id != '')
			if(!$model->Delete("id=$id"))
			{
				return json_encode(array('success' => false, 'msg' => $model->error));
				break;
			}
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $$model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
	
}