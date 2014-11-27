<?php
class ZoneCPController extends Controller
{
	/**
	 * Zone type
	 */
	public $type = array('banner' => 'Banner, button', 'text' => 'Text');
	
	
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
	    $model = new Models_AdsZone();
        if(MULTI_LANGUAGE && !empty($_SESSION['sys_langcode']))
            $model->db->where('lang_code', $_SESSION['sys_langcode']);
		$totalRows = $model->db->count();
	
		$zones = $model->db->select('id,zone_type,name,width,height')
                           ->orderby('name')->limit($pageSize, $offset)->getFieldsArray();
		if(!empty($zones))
		{
			foreach ($zones as $zone)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $zone['id'])));
				$this->tpl->assign('bannerLink', $this->url->action('index', 'BannerCP', array('zone' => $zone['id'])));
				$zone['size'] = $zone['width'] . ' x ' . $zone['height'] . ' (px)';
				$this->tpl->insert_loop('main.zone', 'zone', $zone);
			}
		}
	
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
        $this->tpl->assign('advLink', $this->url->action('index'));
		$this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
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
		$this->tpl->assign('zone_type', $this->html->genSelect('zone_type', $this->type));
        $this->tpl->assign('title', 'Thêm mới');
		return $this->view();
	}
	
	public function createPost(Models_AdsZone $model)
	{
		if (!empty($this->params))
		{
			$data = array(
					'name' => $this->params['name'],
					'zone_type' => $this->params['zone_type'],
					'width' => (int)$this->params['zone-width'],
					'height' => (int)$this->params['zone-height'],
					'lang_code' => $_SESSION['sys_langcode'],
					//'cost' => @$this->params['cost'],
					'desc' => $this->params['desc']
			);
			if($model->Insert($data))
				$this->url->redirectAction('index');
			else
				$this->showError('SQL Error', $model->error);
		}
	}
	
	/**
	 * Sửa thông tin
	 */
	public function editAction()
	{
        $model = new Models_AdsZone();
		$zone = $model->db->where('id', $this->params['id'])->getFields();
		$this->tpl->assign('zone_type', $this->html->genSelect('zone_type', $this->type, $zone['id']));
		$this->tpl->assign('zone', $zone);
		$this->tpl->assign('listLink', $this->url->action('index'));
        $this->tpl->assign('title', $zone['name']);
		return $this->view();
	}
	
	public function editPost(Models_AdsZone $model)
	{
	if (!empty($this->params))
		{
			$data = array(
					'name' => $this->params['name'],
					'zone_type' => $this->params['zone_type'],
					'width' => (int)$this->params['zone-width'],
					'height' => (int)$this->params['zone-height'],
					//'cost' => @$this->params['cost'],
					'desc' => $this->params['desc'],
					'id' => $this->params['key']
			);
	
			if($model->db->where('id', $data['id'])->update($data))
				$this->url->redirectAction('index');
			else
                $this->showError('SQL Error', $model->db->error);
		}
	}
	
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
        $model = new Models_AdsZone();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->db->where('id', $id)->Delete())
				{
					return json_encode(array('success' => false, 'msg' => $model->db->error));
					break;
				}
		}
		elseif ($ids != '')
		{
			if(!$model->db->where('id', $ids)->Delete())
				return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
}