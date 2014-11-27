<?php
class PartnerCPController extends Controller
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
		$model = new Models_Partner();
        if(!empty($_SESSION['sys_langcode']))
            $model->db->where('lang_code', $_SESSION['sys_langcode']);
		$totalRows = $model->db->count();
		
		$partners = $model->db->select('id,name,image,orderno')->orderby('orderno')
                            ->limit($pageSize, $offset)->getFieldsArray();
		
		if(!empty($partners))
		{
			foreach ($partners as $partner)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $partner['id'])));
				$this->tpl->assign('childLink', $this->url->action('index'));
				$this->tpl->insert_loop('main.partner', 'partner', $partner);
			}
		}
		
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		return $this->view();
	}
	
	/**
	 * Thêm mới
	 */
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('form_action', $this->url->action('create'));
        $this->tpl->assign('listLink', $this->url->action('index'));
		return $this->view();
	}
	
	public function createPost(Models_Partner $model)
	{
		if (!empty($this->params))
		{
            $model->lang_code = $_SESSION['sys_langcode'];
	
			if($model->Insert())
				$this->url->redirectAction('index');
			else
				$this->showError('Query error', $model->error);
		}
	}
	
	/**
	 * Sửa thông tin
	 */
	public function editAction()
	{
        $model = new Models_Partner($this->params['id']);
		$this->tpl->assign('listLink', $this->url->action('index'));
		return $this->view($model);
	}
	
	public function editPost(Models_Partner $model)
	{
		if (!empty($this->params))
		{
			$data = array(
					'name' => $this->params['name'],
					'orderno' => $this->params['orderno'],
					'image' => $this->params['image'],
					'link' => $this->params['link'],
					'desc' => $this->params['desc'],
			);

			if($model->db->where('id', $model->id)->update($data))
				$this->url->redirectAction('index');
			else
                $this->showError('Query error', $model->error);
		}
	}
	
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
        $model = new Models_Partner();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->db->where('id', $id)->Delete())
					return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		elseif ($ids != '')
		{
            if(!$model->db->where('id', $ids)->Delete())
                return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
}