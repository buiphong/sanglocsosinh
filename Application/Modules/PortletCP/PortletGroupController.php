<?php
class PortletGroupController extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	public function listAction()
	{
		$model = new Models_PortletGroup();
		$pageSize = 20;
		$recCount = $model->Count();
		
		if (empty($this->params['page']))
			$page = 1;
		else
			$page = $this->params['page'];
		$offset = ($page - 1) * $pageSize;
		
		if(!empty($this->params['txt_search']))
		{
			$search = $this->params['txt_search'];
			$this->url->redirectAction('list','Portlet', $this->params);
		}
		$groups = $model->db->orderby('name')->limit($pageSize, $offset)->getFieldsArray();
		foreach ($groups as $group)
		{
			$this->tpl->assign('portletLink', $this->url->action('list', 'Portlet', 'PortletCP'));
			$this->tpl->assign('editLink', $this->url->action('edit'));
			$this->tpl->insert_loop('main.portletgroup', 'portletgroup', $group);
		}
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $recCount));
		if ($recCount > 0)
			$this->tpl->parse('main.button');
		
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		
		return $this->view();
	}
	
	public function listPost()
	{
		$this->url->redirectAction('list', $this->params);
	}
	
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('form_action', $this->url->action('create'));
		return $this->view();
	}
	
	public function createPost(Models_PortletGroup $model)
	{
		if (!empty($this->params['name']))
		{
			if ($model->Insert())
				$this->url->redirectAction('list');
			else
				die($model->error);
		}
		else
		{
			die('Thông tin nhập vào không hợp lệ');
		}
	}
	
	public function editAction()
	{
		$model = new Models_PortletGroup($this->params['key']);

		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('form_action', $this->url->action('edit'));
		return $this->view($model);
	}
	
	public function editPost(Models_PortletGroup $model)
	{
		if (!empty($this->params))
		{
			if ($model->Update())
				$this->url->redirectAction('list');
			else
				die($model->error);
		}
		else
			die('Dữ liệu chưa chính xác');
	}
	
	function deleteAjax()
	{
		$model = new Models_PortletGroup();
        $mPortlet = new Models_Portlet();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                {
                    if(!$model->Delete("id=$id"))
                        return json_encode(array('success' => false, 'msg' => $model->db->error));
                    $mPortlet->db->where('group_id', $id)->Delete();
                }
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $model->db->error));
            $mPortlet->db->where('group_id', $ids)->Delete();
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('list')));
	}
}