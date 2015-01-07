<?php
class ActionGroupsController extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
		$this->loadLayout('index');
	}
	
	function listAction()
	{
		$pageSize = 20;
		$model = new Models_ActionGroup();
		# page navigation
		$page = @$this->params["page"];
		if ($page=="") $page=1;
		
		# build page menu
		$reccount = $model->db->count();
		$offset = ($page -1) * $pageSize;
		
		# main form
		$arrGroup = $model->db->orderby('name')->limit($pageSize, $offset)->getAll();
		if ($arrGroup == false) {
			$this->showError('Mysql Error', $model->error);
		}
		$rownum = 0;
		foreach ($arrGroup as $group)
		{
			$this->tpl->assign("editLink", $this->url->getUrlAction(array('edit', 'ActionGroups', 'ActionsCP')));
			$this->tpl->assign("actionLink", $this->url->getUrlAction(array('list', 'Actions', 'ActionsCP')));
			$this->tpl->insert_loop('main.actiongroups','actiongroups', $group);
			$rownum++;
		}
		
		$this->tpl->assign("PAGE", Helper::pagging($page, $pageSize, $reccount));

		$this->tpl->assign("createLink", $this->url->getUrlAction(array('create', 'ActionGroups', 'ActionsCP')));
		$this->tpl->assign("listLink", $this->url->getUrlAction(array('list', 'ActionGroups', 'ActionsCP')));
		$this->tpl->assign("deleteLink", $this->url->getUrlAction(array('delete', 'ActionGroups', 'ActionsCP')));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		if ($arrGroup) $this->tpl->parse("main.button");

		return $this->view();
	}
	
	function listPost()
	{
		$this->url->redirectAction(array('list', 'ActionGroups', 'ActionsCP', $this->params));
	}
	
	function createAction()
	{
		$this->setView('edit');
		//Status
		$this->tpl->assign('status', $this->html->genCheckbox('status', 1, 1));
		# form action
		$this->tpl->assign("form_action", $this->url->action('create'));
		$this->tpl->assign("listLink", $this->url->getUrlAction(array('list', 'ActionGroups', 'ActionsCP')));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('group', 'Thêm mới');
		return $this->view();
	}
	
	function createPost(Models_ActionGroup $model)
	{
		# Now update table
		if ($model->Insert()){
			$this->url->redirectAction('list');
		} else {
			$this->showError('Mysql Error', $model->error);
		}
	}
	
	function editAction()
	{
		$key = @$this->params["key"]; //key parameter
		$model = new Models_ActionGroup($key);
		# action
		$this->tpl->assign("form_action", $this->url->getUrlAction(array('edit', 'ActionGroups', 'ActionsCP')));
		$this->tpl->assign("listLink", $this->url->getUrlAction(array('list', 'ActionGroups', 'ActionsCP')));
		//Status
		$this->tpl->assign('status', $this->html->genCheckbox('status', 1, $model->status));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('group', $model->name);
		return $this->view($model);
	}
	
	function editPost(Models_ActionGroup $model)
	{
		if ($model->Update()){
			$this->url->redirectAction('list');
		} else {
			$this->showError('Mysql Error', $model->error);
		}
	}
	
	function deleteAjax()
	{
		$model = new Models_ActionGroup();
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
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->getUrlAction(array('list', 'ActionGroups', 'ActionsCP'))));
	}
}