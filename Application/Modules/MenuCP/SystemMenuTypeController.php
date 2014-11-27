<?php
class SystemMenuTypeController extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('flatadmin');
		$this->loadLayout('index');
	}
	
	function listAction()
	{
		$modelType = new Models_SystemMenuType();
		$pageSize = 20;
		# anchor parent
		$parentid = @$this->params["parentid"];
		$where = "";
		# page navigation
		$page = @$this->params["page"];
		if ($page=="") $page=1;
		$offset = ($page -1) * $pageSize;
		# build page menu
		$reccount = $modelType->Count($where);
		
		# main form
		$types = $modelType->db->where($where)->orderby('type_name')->limit($pageSize,$offset)->getAll();
		foreach ($types as $type)
		{
			$this->tpl->assign('editLink', $this->url->action('edit'));
			$this->tpl->assign('listMenu', $this->url->action('list', 'SystemMenu', array('type' => $type['id'])));
			$this->tpl->insert_loop('main.type', 'type', $type);
		}
		
		$this->tpl->assign("PAGE", Helper::pagging($page, $pageSize, $reccount));

		if ($type) $this->tpl->parse("main.button");

		$this->tpl->assign('addLink', $this->url->action('add'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		return $this->view();
	}
	
	function addAction()
	{
		$this->setView('edit');
		$model = new Models_SystemMenuType();
		# form action
		$this->tpl->assign("form_action", $this->url->action('add'));
		return $this->view();
	}
	
	function addPost(Models_SystemMenuType $model)
	{
		if ($model->Insert()){
			$this->url->redirectAction('list');
		} else {
			$msg = "Can not insert data: ".$this->model->error;
			echo $msg;die;
		}
	}
	
	function editAction()
	{
		$key = @$this->params["key"]; //key parameter
		$model = new Models_SystemMenuType($key);
		$this->tpl->assign('listLink', $this->url->action('list'));
		return $this->view($model);
	}
	
	function editPost(Models_SystemMenuType $model)
	{
		if ($model->Update()){
			$this->url->redirectAction('list');
		} else {
			die("Can not update data: ".$this->model->error);
		}
	}
	
	function deleteAjax()
	{
		$model = new Models_SystemMenuType();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                    if(!$model->Delete("id='$id'"))
                        return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id='$ids'"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true));
	}
}//end of class
?>