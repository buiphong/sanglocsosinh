<?php
class CustomPortlet extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('admin');
	}
	
	public function indexAction()
	{
		$pageSize = 20;
	
		$recCount = $this->db->GetFieldValue("select count(id) as total from custom_portlet");
	
		if (empty($this->params['page']))
			$page = 1;
		else
			$page = $this->params['page'];
		$offset = ($page - 1) * $pageSize;
	
		$portlets = $this->db->getFieldsArray("select * from custom_portlet order by title asc limit $offset, $pageSize");
		foreach ($portlets as $portlet)
		{
			$this->tpl->assign('editLink', $this->url->action('edit'));
			$this->tpl->insert_loop('main.portlet', 'portlet', $portlet);
		}
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $recCount));
		if ($recCount > 0)
			$this->tpl->parse('main.button');
	
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
	
		return $this->view();
	}
	
	public function indexPost()
	{
		$this->url->redirectAction('list', $this->params);
	}
	
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('form_action', $this->url->action('create'));
		return $this->view();
	}
	
	public function createPost()
	{
		if (!empty($this->params['title']))
		{
			$data = array(
					'title' => $this->params['title'],
					'description' => $this->params['description'],
					'url' => $this->params['url'],
					'values' => $this->params['values']
			);
			if ($this->db->Insert('custom_portlet', $data))
				$this->url->redirectAction('index');
			else
				die($this->db->ErrorMsg());
		}
		else
		{
			die('Thông tin nhập vào không hợp lệ');
		}
	}
	
	public function editAction()
	{
		$portlet = $this->db->getFields('select * from custom_portlet where id=' . $this->params['key']);
		$this->tpl->assign('portlet', $portlet);
		return $this->view();
	}
	
	public function editPost()
	{
		if (!empty($this->params['title']))
		{
			$data = array(
					'title' => $this->params['title'],
					'description' => $this->params['description'],
					'url' => $this->params['url'],
					'values' => $this->params['values'],
					'id' => $this->params['key']
			);
			if ($this->db->Update('custom_portlet', $data, 'id'))
				$this->url->redirectAction('index');
			else
				die($this->db->ErrorMsg());
		}
		else
		{
			die('Thông tin nhập vào không hợp lệ');
		}
	}
	
	function deleteAjax()
	{
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                    if(!$this->db->Delete('custom_portlet', "id=$id"))
                        return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		elseif ($ids != '')
		{
			if(!$this->db->Delete('custom_portlet', "id=$ids"))
				return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('list')));
	}
}