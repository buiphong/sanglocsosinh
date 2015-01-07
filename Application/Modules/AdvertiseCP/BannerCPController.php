<?php
class BannerCPController extends Controller
{
	/**
	 * Banner type
	 */
	public $type = array('image' => 'Image', 'flash' => 'Flash', 'text' => 'Text');
	
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
	    $model = new Models_AdsBanner();
        if(!empty($_SESSION['sys_langcode']))
            $model->db->where('ads_banner.lang_code', $_SESSION['sys_langcode']);
		if (!empty($this->params['zone']))
            $model->db->where('ads_banner.zone_id', $this->params['zone']);
		
		$totalRows = $model->db->count();
	
		$banners = $model->db->select('ads_banner.width,ads_banner.height,ads_banner.file_data,ads_banner.id,ads_banner.banner_type,ads_banner.name,ads_banner.orderno,ads_zone.name as zone')
                        ->join('ads_zone', 'ads_banner.zone_id=ads_zone.id')->orderby('ads_banner.orderno')
                        ->limit($pageSize, $offset)->getFieldsArray();
		if(!empty($banners))
		{
			foreach ($banners as $banner)
			{
                if(is_file(Url::getAppDir() . DIRECTORY_SEPARATOR . $banner['file_data']))
                {
                    $i = getimagesize(Url::getAppDir() . DIRECTORY_SEPARATOR . $banner['file_data']);
                    $banner['size'] = $banner['width'].'x'.$banner['height'] . ' (px)';
                    $banner['real_size'] = $i[0] . 'x' . $i[1];
                }
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $banner['id'], 'zone' => @$this->params['zone'])));
				$this->tpl->insert_loop('main.banner', 'banner', $banner);
			}
		}
	
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create', array('zone' => @$this->params['zone'])));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
        $this->tpl->assign('advLink', $this->url->action('index'));
		$this->tpl->assign('sidebarZone', $this->renderAction(array('getZoneSidebar', 'BannerCP', 'AdvertiseCP')));
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
		$this->tpl->assign('banner_type', $this->html->genSelect('banner_type', $this->type));

        $modelZone = new Models_AdsZone();
		//Get zone
		/* ZONE */		
		$zones = $modelZone->db->getcFieldsArray();
		foreach ($zones as $zone)
		{
			$arrZone[$zone['id']] = $zone['name'];
		}
		$zone_id = @$this->params['zone'];
		$this->tpl->assign('zone', $this->html->genSelect('zone', $arrZone, $zone_id));
				
		$banner = array('zone_id' => @$this->params['zone'], 'width' => @$zone['width'], 'height' => @$zone['height']);
		$this->tpl->assign('banner', $banner);
		return $this->view();
	}
	
	public function createPost(Models_AdsBanner $model)
	{
	if (!empty($this->params))
		{
			$data = array(
					'name' => $this->params['name'],
					'zone_id' => $this->params['zone'],
					'width' => (int)$this->params['banner-width'],
					'height' => (int)$this->params['banner-height'],
					'lang_code' => $_SESSION['sys_langcode'],
					'link' => $this->params['link'],
					'desc' => $this->params['desc'],
					'banner_type'=> $this->params['banner_type'],
					'file_data' => $this->params['file_data']
			);

            if(is_file(Url::getAppDir() . DIRECTORY_SEPARATOR . $data['file_data']))
            {
                $i = getimagesize(Url::getAppDir() . DIRECTORY_SEPARATOR . $data['file_data']);
                $banner['real_width'] = $i[0];
                $banner['real_height'] = $i[1];
            }
			
			if ($this->params['status'] == 1) {
				$data['status'] = 1;
			}
			else
				$data['status'] = 0;
			if($model->Insert($data))
				$this->url->redirectAction('index', array('zone' => $this->params['zone_id']));
			else
                $this->showError('SQL Error', $model->db->error);
		}	
	}
	
	/**
	 * Sửa thông tin
	 */
	public function editAction()
	{
        $model = new Models_AdsBanner();
        $modelZone = new Models_AdsZone();
		$this->tpl->assign('form_action', $this->url->action('edit'));
		$banner = $model->db->where('id', $this->params['id'])->getFields();

		$this->tpl->assign('listLink', $this->url->action('index', array('zone' => $banner['zone_id'])));
		/* ZONE */
		$zones = $modelZone->db->getcFieldsArray();
		foreach ($zones as $zone)
		{
			$arrZone[$zone['id']] = $zone['name'];
		}
		if ($banner['status'] == '1') {
			$this->tpl->assign('checked', 'checked');
		}
		else 
			$this->tpl->assign('checked', '');
		$this->tpl->assign('zone', $this->html->genSelect('zone_id', $arrZone, $banner['zone_id']));

        if(is_file(Url::getAppDir() . DIRECTORY_SEPARATOR . $banner['file_data']))
        {
            $i = getimagesize(Url::getAppDir() . DIRECTORY_SEPARATOR . $banner['file_data']);
            $banner['real_size']['width'] = $i[0];
            $banner['real_size']['height'] = $i[1];
        }
        $this->tpl->assign('banner', $banner);
		$this->tpl->assign('banner_type', $this->html->genSelect('banner_type', $this->type, $banner['banner_type']));
		return $this->view();
	}
	
	public function editPost(Models_AdsBanner $model)
	{
		if (!empty($this->params))
			{
				$data = array(
						'name' => $this->params['name'],
						'zone_id' => $this->params['zone_id'],
						'width' => (int)$this->params['banner-width'],
						'height' => (int)$this->params['banner-height'],
						'lang_code' => @$_SESSION['sys_langcode'],
						'link' => $this->params['link'],
						'desc' => $this->params['desc'],
						'banner_type'=> $this->params['banner_type'],
						'file_data' => $this->params['file_data'],
                        'orderno' => $this->params['orderno']
				);
				if ($model->status == 1) {
					$data['status'] = 1;
				}
				else 
					$data['status'] = 0;

                if(is_file(Url::getAppDir() . DIRECTORY_SEPARATOR . $data['file_data']))
                {
                    $i = getimagesize(Url::getAppDir() . DIRECTORY_SEPARATOR . $data['file_data']);
                    $banner['real_width'] = $i[0];
                    $banner['real_height'] = $i[1];
                }

				if($model->db->where('id', $this->params['key'])->update($data))
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
        $model = new Models_AdsBanner();
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
	
	/**
	 * Sidebar Zone
	 */
	public function getZoneSidebarAction()
	{
        $model = new Models_AdsZone();
		$zones = $model->db->select('id,name')->getFieldsArray();
		foreach ($zones as $zone)
		{
			$zone['link'] = $this->url->action('index', array('zone' => $zone['id']));
			$this->tpl->insert_loop('main.zone', 'zone', $zone);
		}
		$this->unloadLayout();
		return $this->view();
	}

    public function getSizeBannerAjax()
    {
        if(is_file(Url::getAppDir() . DIRECTORY_SEPARATOR . $this->params['file']))
        {
            $i = getimagesize(Url::getAppDir() . DIRECTORY_SEPARATOR . $this->params['file']);
            return json_encode(array('success' => true, 'width' => $i[0], 'height' => $i[1]));
        }
        return json_encode(array('sucess' => false));
    }
}