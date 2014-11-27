<?php
class MenuPortletController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
        $this->loadModule('PortletCP');
	}
	
	public function listAction()
	{
		$model = new Models_MenuPortlet();
		//Lấy danh sách portlet theo menu
		if (!empty($this->params['menuid']))
		{
			$portlets = $model->db->select('menu_portlet.*,portlets.title as portlet')  
								->join('portlets', 'portlets.id=menu_portlet.portlet_id') 
								->where('menu_portlet.menu_id', $this->params['menuid'])
								->orderby('menu_portlet.orderno')->getAll();
			if (!empty($portlets))
			{
				foreach ($portlets as $portlet)
				{
					$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $portlet['id'], 
							'menuid' => $this->params['menuid'])));
					$this->tpl->insert_loop('main.portlet', 'portlet', $portlet);
				}
			}
			if (count($portlets) > 0)
				$this->tpl->parse('main.button');
			
			$modelMenu = new Models_Menu($this->params['menuid']);
			$this->tpl->assign('menu', $modelMenu->title);
		}
		
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('createLink', $this->url->action('create', array('menuid' => $this->params['menuid'])));
		
		return $this->view();
	}
	
	/**
	 * Thêm mới menu
	 */
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('list', array('menuid' => $this->params['menuid'])));
		$this->tpl->assign('form_action', $this->url->action('create'));
	
		return $this->view();
	}
	
	public function createPost()
	{
		if (!empty($this->params))
		{
			$data = array(
					'title' => $this->params['title'],
					'orderno' => $this->params['orderno'],
					//'status' => $this->params['status'],
					'template' => $this->params['template'],
					'layout' => $this->params['layout'],
			);
			if ($this->db->Insert('menu_portlet', $data))
				$this->url->redirectAction('list');
			else
				die($this->db->ErrorMsg());
		}
		else
			die('Dữ liệu chưa chính xác');
	}
	
	/**
	 * Sửa menu
	 */
	public function editAction()
	{
		if (!empty($this->params['id']))
		{
			$model = new Models_Menu(array('id'=>$this->params['id']));
		}
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('form_action', $this->url->action('edit'));		
	
		return $this->view($model);
	}
	
	public function editPost()
	{
		if (!empty($this->params))
		{
			$data = array(
					'title' => $this->params['title'],
					'orderno' => $this->params['orderno'],
					//'status' => $this->params['status'],
					'template' => $this->params['template'],
					'layout' => $this->params['layout'],
					'id' => $this->params['pkey']
			);
			if ($this->db->Update('menu_portlet', $data, 'id'))
				$this->url->redirectAction('list');
			else
				die($this->db->ErrorMsg());
		}
		else
			die('Dữ liệu chưa chính xác');
	}
	
	function deleteAjax()
	{
		$model = new Models_MenuPortlet();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->Delete("id='$id'"))
				{
					return json_encode(array('success' => false, 'msg' => $model->db->error));
					break;
				}
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id='$ids'"))
				return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		return json_encode(array('success' => true));
	}
}