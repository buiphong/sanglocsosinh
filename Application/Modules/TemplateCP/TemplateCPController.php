<?php
class TemplateCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}

	/**
	 * Danh mục web
	 */
	public function indexAction()
	{
		$pageSize = 20;

		$page = @$this->params['page'];
		if (empty($page))
			$page = 1;

		$offset = ($page - 1) * $pageSize;
		$model = new Models_Template();
		$totalRows = $model->db->count();
		$templates = $model->db->limit($pageSize, $offset)->getAll();

		if(!empty($templates))
		{
			foreach ($templates as $template)
			{				
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $template['id'])));
                $this->tpl->assign('defaultPortletLink',
                    $this->url->action('getDefaultPortlet', 'PortletDefaultCP', 'PortletCP', array('template' => $template['name'])));
				$this->tpl->insert_loop('main.template', 'template', $template);
			}
		}

		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create', array('parentid' => @$this->params['parentid'])));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('parentName', "Danh sách template");
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('listLink', $this->url->action('index'));
		return $this->view();
	}

	/**
	 * Thêm mới danh mục
	 */
	public function createAction()
	{
		$this->setView('edit');
		$model = new Models_Template();
		$Temp = $model->db->select('name')->getFieldArray();
		if(!empty($Temp))
		{
			foreach ($Temp as $vl)
			{
				array_push($this->removeTemp, $vl);
			}
		}		
		$arrTemp = $this->getTemplates();
		$this->tpl->assign('template',$this->html->genSelect('name', $arrTemp,'','name','name'));
		
		$this->tpl->assign('isdefault', $this->html->genCheckbox('isdefault', 1));
		$this->tpl->assign('catName', 'Thêm mới');		
		$this->tpl->assign('listLink',$this->url->action('index'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
		return $this->view();
	}

	public function createPost(Models_Template $model)
	{
		if($model->isdefault == 1)
		{
			$model->db->where('isdefault',1)->update(array('isdefault'=>0));
		}
		$modelUser = new Models_User();
		$user = $modelUser->db->select('id,fullname')->where('username',$_SESSION['system_username'])->getFields();
		$model->userid = $user['id'];
		$model->createdby = $user['fullname'];
		$model->createddate = date('Y:m:d H:i');
		if($model->Insert())
			$this->url->redirectAction('index');
		else
			$this->showError('Mysql Error', $model->db->error);
	}

	/**
	 * Sửa thông tin danh mục
	 */
	public function editAction()
	{
		$model = new Models_Template($this->params['id']);
		$Temp = $model->db->select('name')->where_not_in('id',$model->id)->getFieldArray();
		if(!empty($Temp))
		{
			foreach ($Temp as $vl)
			{
				array_push($this->removeTemp, $vl);
			}
		}	
		$arrTemp = $this->getTemplates();
		$this->tpl->assign('template',$this->html->genSelect('name', $arrTemp,$model->name,'name','name'));
	
		$this->tpl->assign('isdefault', $this->html->genCheckbox('isdefault', 1,$model->isdefault));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('catName', $model->name);
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
		return $this->view($model);
	}

	public function editPost(Models_Template $model)
	{
		if($model->isdefault == 1)
		{
			$model->db->where('isdefault',1)->update(array('isdefault'=>0));
		}
		if($model->Update())
			$this->url->redirectAction('index', array('parentid' => $model->parent_id));
		else
			$this->showError('Mysql Error', $model->db->error);
	}

	function deleteAjax()
	{
		$model = new Models_Template();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
			{
				if ($id != '')
				{
					if(!$model->Delete("id = $id"))
						return json_encode(array('success' => false, 'msg' => $model->error));
				}
			}
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id = $ids"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
	/**
	 * Lấy danh sách template
	 * @return array
	 */
	public static function getTemplates()
	{
		$ignoredDir = array('.', '..', 'flatadmin');
		$listDir = VccDirectory::getSubDirectories((Url::getAppDir().'Templates'), $ignoredDir);
		foreach ($listDir as $key => $dir)
		{
			$list[$key] = array('name' => $key, 'path' => $dir);
		}
		return $list;
	}	
}








