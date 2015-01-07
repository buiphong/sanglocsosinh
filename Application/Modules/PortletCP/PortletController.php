<?php
class PortletController extends Controller
{
	public $paramsType = array('newsid' => 'Tin bài', 'ncatid' => 'Danh mục tin', 'productid' => 'Sản phẩm',
			'pcatid' => 'Danh mục sản phẩm', 'aid' => 'Chi tiết trang nội dung');
	
	public $types = array('portlet' => 'Portlet', 'custom_portlet' => 'Portlet dựng sẵn');
	
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Danh sách portlet
	 */
	public function listAction()
	{
		$pageSize = 20;
		$model = new Models_Portlet();
		if (empty($this->params['page']))
			$page = 1;
		else
			$page = $this->params['page'];
		$offset = ($page - 1) * $pageSize;
		
		$where = '';
		if (!empty($this->params['groupid']))
		{
			$model->db->where('group_id', $this->params['groupid']);
			$this->tpl->assign('groupid',$this->params['groupid']);
		}
		if(!empty($this->params['txt_search']))
		{
			$arrS = array('title','module','controller','action');
			$search = $this->params['txt_search'];
			$where = "";
			foreach ($arrS as $vl)
			{
				$where = " `$vl` like '%$search%' OR ".$where;				
			}
			$where = substr($where,0,strlen($where)-3);
			if(!empty($where))
			{
				$model->db->where("($where)");
			}
			$this->tpl->assign("textSearch",$search);
		}
		$recCount = $model->db->count();
		$portlets = $model->db->orderby('title')->limit($pageSize, $offset)->getFieldsArray();
		foreach ($portlets as $portlet)
		{
			$this->tpl->assign('editLink', $this->url->action('edit', 
					array('groupid' => @$this->params['groupid'], 'key' => $portlet['id'])));
			$this->tpl->assign('paramLink', $this->url->action('list', 'PortletParams',
					array('groupid' => @$this->params['groupid'], 'portletid' => $portlet['id'])));
			$this->tpl->insert_loop('main.portlet', 'portlet', $portlet);
		}
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $recCount));
		if ($recCount > 0)
			$this->tpl->parse('main.button');
		
		$this->tpl->assign('groupLink', $this->url->action('list', 'PortletGroup'));
		$this->tpl->assign('listLink', $this->url->action('list', array('groupid' => @$this->params['groupid'])));
		$this->tpl->assign('createLink', $this->url->action('create', $this->params));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		
		return $this->view();
	}
	public function listPost()
	{
		$this->url->redirectAction('list', $this->params);
	}
	/**
	 * Thêm mới portlet
	 */
	public function createAction()
	{
		$this->setView('edit');
		
		$listTemplates = $this->getTemplates();
		$this->tpl->assign('template', $this->html->genSelect('template', $listTemplates, '', 'name', 'name', array('style' => 'width: 278px', 'class' => 'field'), '.....', true));
		
		//layout
		$keys = array_keys($listTemplates);
		$layouts = $this->getLayout($keys[0]);
		$this->tpl->assign('layout', $this->html->genSelect('layout', $layouts, '', '', '', array('style' => 'width: 278px', 'class' => 'field'), '.....', true));
		
		$this->tpl->assign('listLink', $this->url->action('list', array('groupid' => @$this->params['groupid'])));
		$this->tpl->assign('type', $this->html->genSelect('type', $this->types));
		return $this->view();
	}
	
	public function createPost(Models_Portlet $model)
	{
		if (!empty($this->params['edit_view']))
			$model->edit_view = $this->params['edit_view'];
		
		if (!empty($this->params['groupid']))
			$model->group_id = $this->params['groupid'];

		if ($model->Insert()) {
			$this->url->redirectAction('list', array('groupid' => $model->group_id));
		}
		else
			die($this->db->ErrorMsg());
	}
	
	/**
	 * Sửa portlet
	 */
	public function editAction()
	{
		$portlet = new Models_Portlet($this->params['key']);
		if ($portlet->edit_view == 1)
			$this->tpl->assign('checked', 'checked');
		else
			$this->tpl->assign('checked', '');
		$this->tpl->assign('type', $this->html->genSelect('type', $this->types, $portlet->type));
		
		$listTemplates = $this->getTemplates();
		$this->tpl->assign('template', $this->html->genSelect('template', $listTemplates, $portlet->template, 'name', 'name', array('style' => 'width: 278px', 'class' => 'field'), '.....', true));
		
		//layout
		$layouts = $this->getLayout($portlet->template);
		$this->tpl->assign('layout', $this->html->genSelect('layout', $layouts, $portlet->layout, '', '', array('style' => 'width: 278px', 'class' => 'field'), '.....', true));

		$this->tpl->assign('frm_action', $this->url->action('edit', array('groupid' => @$this->params['groupid'])));
		$this->tpl->assign('listLink', $this->url->action('list', array('groupid' => @$this->params['groupid'])));
		return $this->view($portlet);
	}
	
	public function editPost(Models_Portlet $model)
	{
		if (!empty($this->params['edit_view']))
			$model->edit_view = $this->params['edit_view'];
		
		if ($model->Update())
            $this->url->redirectAction('list', array('groupid' => $this->params['groupid']));
		else
			$this->showError('Mysql Error', $model->db->error);

	}
	
	
	public function deleteAjax()
	{
		$model = new Models_Portlet();
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
		return json_encode(array('success' => true, 'link' => $this->url->action('list')));
	}
	
	/**
	 * Add params portlet
	 */
	public function addParamPortletAjax()
	{
		$this->unloadLayout();
		foreach ($this->paramsType as $type)
		{
			$this->tpl->insert_loop('main.type', 'type', $type);
		}
		return json_encode(array('success' => true, 'html' => $this->view()));
	}
	
	public function loadDetailParamsAjax()
	{
		$langCode = 'vi-VN';
		$html = '';
		switch($this->params['type'])
		{
			case 'newsid':
				//Lấy danh sách tin tức
				$news = $this->db->getcFieldArray("select id, title from news 
					where status=1 and lang_code='$langCode' order by created_date desc");
				$html = $this->html->genSelect('detailParam', $news, '', 'id', 'title');
				break;
			case 'ncatid':
				//lấy danh sách danh mục tin
				$cats = $this->db->getcFieldsArray("select id, title from news_category where lang_code='$langCode'");
				$html = $this->html->genSelect('detailParam', $cats, '', 'id', 'title');
				break;
			case 'productid':
				//Lấy danh sách sản phẩm
				$arr = $this->db->getcFieldArray("select id, name from product
						where status=1 and lang_code='$langCode' order by create_date desc");
				$html = $this->html->genSelect('detailParam', $arr, '', 'id', 'name');
				break;
			case 'pcatid':
				//Lấy danh sách danh mục sản phẩm
				$arr = $this->db->getcFieldArray("select id, title from product_category
						where status=1 and lang_code='$langCode'");
				$html = $this->html->genSelect('detailParam', $arr, '', 'id', 'title');
				break;
			case 'aid':
				//Lấy danh sách bài viết
				$arr = $this->db->getcFieldsArray("select id, title from artical where lang_code='$langCode' 
						order by create_date desc");
				$html = $this->html->genSelect('detailParam', $arr, '', 'id', 'title');
				break;
		}
		return json_encode(array('success' => true, 'html' => $html));
	}
	
	/**
	 * Update view content
	 */
	public function updateViewContentAjax()
	{
		if(file_put_contents($this->params['viewFile'], stripcslashes($this->params['content'])))
			return json_encode(array('success' => true));
		else
			return json_encode(array('success' => false, 'file' => $this->params['viewFile']));
        return json_encode(array('success' => true));
	}
	
	/**
	 * get master table
	 */
	function getMasterTableAction()
	{
		$this->unloadLayout();
		if (!empty($this->params['groupid']))
		{
			$group = new Models_PortletGroup($this->params['groupid']);
			$this->tpl->assign('groupname', $group->name);
			$this->tpl->assign('groupLink', $this->url->action('list', 'PortletGroup', 'portlet'));
		}
		return $this->view();
	}
	
	public function checkModuleAjax()
	{
		if (!is_dir(Url::getAppDir(). $this->params['module']))
			return json_encode(array('success' => false));
		else
			return json_encode(array('success' => true));
	}
	
	/**
	 * Lấy danh sách template
	 * @return array
	 */
	public static function getTemplates()
	{
		$ignoredDir = array('flatadmin');
		$listDir = PTDirectory::getSubDirectories((Url::getAppDir().TEMPLATE_DIR),$ignoredDir);
		foreach ($listDir as $key => $dir)
		{
			$listDir[$key] = array('name' => $key, 'path' => $dir);
		}
		return $listDir;
	}
	
	/**
	 * lấy danh sách layout
	 */
	public static function getLayout($template)
	{
        if(empty($template))
            return false;
		//check dir
		if (!is_dir(Url::getAppDir().TEMPLATE_DIR.DIRECTORY_SEPARATOR . $template))
			return false;
		$list = scandir(Url::getAppDir().TEMPLATE_DIR.DIRECTORY_SEPARATOR . $template . '/layout');
		$ignoredItem = array('.', '..','.svn');
		$arrItem = array();
		foreach ($list as $item)
		{
			if (!(array_search($item, $ignoredItem) > -1))
			{
				$item = substr($item, 0, -4);
				$arrItem[$item] = $item;
			}
		}
		return $arrItem;
	}
	
	public function getLayoutAjax()
	{
		if (!empty($this->params['template']))
		{
			$layout = $this->getLayout($this->params['template']);
			if ($layout)
				return json_encode(array('success' => true, 'html' => $this->html->genSelect('layout', $layout, '', '', '', array('style' => 'width: 278px', 'class' => 'field'))));
			else
				return json_encode(array('success' => true, 'html' => $this->html->genSelect('layout', array(), '', '', '', array('style' => 'width: 278px', 'class' => 'field'), '.....', true)));
		}
		return json_encode(array('success' => true, 'html' => $this->html->genSelect('layout', array(), '', '', '', array('style' => 'width: 278px', 'class' => 'field'), '.....', true)));
	}

    /**
     * add portlet
     */
    public function addPortletAjax()
    {
        $this->unloadLayout();
        $model = new Models_PortletGroup();
        //Lấy danh sách nhóm portlet
        $groups = $model->db->getFieldsArray();
        foreach ($groups as $group)
        {
            $this->tpl->insert_loop('main.group', 'group', $group);
        }
        //Lấy danh sách portlet
        $modelPortlet = new Models_Portlet();
        $portlets = $modelPortlet->db->select('id,title,module,controller,action,group_id')
            ->where('title <>', '')
            ->orderby('title')->getFieldsArray();

        if ($portlets)
        {
            foreach ($portlets as $portlet)
            {
                if(Helper::moduleExist($portlet['module']))
                {
                    $this->tpl->assign('region', $this->params['region']);
                    $this->tpl->assign('itemid', $this->params['itemid']);
                    $this->tpl->assign('routerid', @$this->params['routerid']);
                    $this->tpl->assign('type', $this->params['type']);
                    $this->tpl->insert_loop('main.portlet', 'portlet', $portlet);
                }
            }
        }
        return json_encode(array('success' => true, 'html' => $this->view()));
    }

    public function addPortletPostAjax()
    {
        $result = array('success' => false);
        if (!empty($this->params['itemid']))
        {
            if ($this->params['type'] == 'menu')
            {
                $result = Models_MenuPortlet::addPortlet($this->params);
                //get content portlet
                $portlet = Models_MenuPortlet::getPortletInfo($result['id']);
                $pMax = Models_MenuPortlet::getMaxOrderNoByRegion($portlet['menu_id'],$portlet['container_id']);
                $result['html'] = PortletHelper::getPortletCPButton($result['id'], $portlet['orderno'], $pMax, 'menu', $portlet['edit_view']).
                        PortletHelper::getPortletView($portlet);
            }
            elseif ($this->params['type'] == 'router')
            {
                $result = Models_RouterPortlet::addPortlet($this->params);
                //get content portlet
                $portlet = Models_RouterPortlet::getPortletInfo($result['id']);
                $pMax = Models_RouterPortlet::getMaxOrderNoByRegion($portlet['router_id'],$portlet['container_id']);
                $result['html'] = PortletHelper::getPortletCPButton($result['id'], $portlet['orderno'], $pMax, 'router', $portlet['edit_view']).
                    PortletHelper::getPortletView($portlet);
            }
        }
        return json_encode($result);
    }

    /**
     * Edit portlet
     */
    public function editPortletAjax()
    {
        $this->unloadLayout();
        if($this->params['type'] == 'menu')
        {
            //Lấy thông tin portlet
            if (!empty($this->params['portletId']))
            {
                $portlet = Models_MenuPortlet::getById($this->params['portletId']);
                //Lấy tham số portlet
                $modelParams = new Models_PortletParam();
                $params = $modelParams->db->select('id,title,name,type,options')
                    ->where('portlet_id', $portlet['portlet_id'])->getFieldsArray();
                $portletParams = new PortletParamsController();
                foreach ($params as $param)
                {
                    $param['valueBox'] = $portletParams->getValueBox($param, $portlet['params']);
                    $this->tpl->insert_loop('main.param', 'param', $param);
                }
                $portlet['values'] = base64_decode($portlet['values']);
                $this->tpl->assign('portlet', $portlet);
            }
            $this->tpl->assign('type', 'menu');
        }
        elseif($this->params['type'] == 'router')
        {
            //Lấy thông tin portlet
            if (!empty($this->params['portletId']))
            {
                $portlet = Models_RouterPortlet::getById($this->params['portletId']);
                //Lấy tham số portlet
                $modelParams = new Models_PortletParam();
                $params = $modelParams->db->select('id,title,name,type,options')
                    ->where('portlet_id', $portlet['portlet_id'])->getFieldsArray();
                $portletParams = new PortletParamsController();
                foreach ($params as $param)
                {
                    $param['valueBox'] = $portletParams->getValueBox($param, $portlet['params']);
                    $this->tpl->insert_loop('main.param', 'param', $param);
                }

                $this->tpl->assign('portlet', $portlet);
            }
            $this->tpl->assign('type', 'router');
        }
        $this->tpl->assign('paramsType', $this->html->genSelect('paramsType', $this->paramsType));
        //Lấy danh sách skin
        $skins = PTDirectory::getFilesDir('Skins');
        $this->tpl->assign('skin', $this->html->genSelect('skin', $skins, $portlet['skin'], '', '',
            array('class' => 'chosen-select'), 'Default skin', true));
        return json_encode(array('success' => true, 'html' => $this->view()));
    }

    public function editPortletPostAjax()
    {
        if($this->params['type'] == 'menu')
        {
            $data = array('params' => $this->params['params'], 'title' => $this->params['title'],
                /*'values' => base64_encode(stripcslashes($this->params['values'])), */
                'skin' => $this->params['skin'],
                'cache_time' => $this->params['cache_time']);
            $model = new Models_MenuPortlet();
            if(!empty($this->params['portletId']))
            {
                if ($model->db->where('id', $this->params['portletId'])->update($data))
                {
                    return json_encode(array('success' => true,
                        /*'values' => $this->params['values'],*/
                        'html' => PortletHelper::getPortletCPView($this->params['portletId'], 'menu')));
                }
                else
                    return json_encode(array('success' => false, 'msg' => $model->error));
            }
            else
                return json_encode(array('success' => false, 'msg' => 'Không tìm thấy thông tin portlet'));
        }
        elseif($this->params['type'] == 'router')
        {
            $mRouterPortlet = new Models_RouterPortlet();
            $data = array('params' => $this->params['params'], 'title' => $this->params['title'],
                /*'values' => $this->params['values'], */
                'skin' => $this->params['skin'],
                'cache_time' => $this->params['cache_time']);
            if ($mRouterPortlet->db->where('id', $this->params['portletId'])->update($data))
            {
                return json_encode(array('success' => true,
                    'html' => PortletHelper::getPortletCPView($this->params['portletId'], 'router')));
            }
            else
                return json_encode(array('success' => false, 'msg' => $mRouterPortlet->error));
        }
        return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa đúng'));
    }

    /**
     * move up portlet
     */
    public function moveUpPortletAjax()
    {
        if (!empty($this->params['portletid']))
        {
            if ($this->params['type'] == 'menu')
            {
                $result = Models_MenuPortlet::moveUp($this->params['portletid']);
                return json_encode($result);
            }
            elseif ($this->params['type'] == 'router')
            {
                $result = Models_RouterPortlet::moveUp($this->params['portletid']);
                return json_encode($result);
            }
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Thông tin portlet không đúng'));
    }

    /**
     * Move down portlet
     */
    public function moveDownPortletAjax()
    {
        if (!empty($this->params['portletid']))
        {
            if ($this->params['type'] == 'menu')
            {
                $result = Models_MenuPortlet::moveDown($this->params['portletid']);
                return json_encode($result);
            }
            elseif ($this->params['type'] == 'router')
            {
                $result = Models_RouterPortlet::moveDown($this->params['portletid']);
                return json_encode($result);
            }
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Thông tin portlet không đúng'));
    }

    public function removePortletAjax()
    {
        if (!empty($this->params['portletId']))
        {
            if ($this->params['type'] == 'menu')
            {
                $result = Models_MenuPortlet::remove($this->params['portletId']);
                return json_encode($result);
            }
            elseif ($this->params['type'] == 'router')
            {
                $result = Models_RouterPortlet::remove($this->params['portletId']);
                return json_encode($result);
            }
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Không tìm thấy portlet tương ứng'));
    }

    function editViewFileAjax()
    {
        $this->unloadLayout();
        $model = new Models_Portlet();
        if($this->params['type'] == 'menu')
        {
            //get portlet
            $portlet = $model->db->select('portlets.module,portlets.controller,portlets.action,menu_portlet.skin')
                ->join('menu_portlet', 'menu_portlet.portlet_id=portlets.id')
                ->where('menu_portlet.id', $this->params['menuid'])->getFields();
        }
        elseif($this->params['type'] == 'router')
        {
            //get portlet
            $portlet = $model->db->select('portlets.module,portlets.controller,portlets.action,router_portlet.skin')
                ->join('router_portlet', 'router_portlet.portlet_id=portlets.id')
                ->where('router_portlet.id', $this->params['menuid'])->getFields();
        }

        if($portlet)
        {
            //Get view file
            if (!empty($portlet['skin'])) {
                $viewFile = $this->url->getUrlContent(SKIN_DIR . '/' . $portlet['skin']);
            }
            else
                $viewFile = $this->url->getUrlContent('Application/Portal/' . $portlet['module'] . '/forms/' . $portlet['controller'] . '/' . $portlet['action'] . '.htm');
            $this->tpl->assign('contentFile', htmlspecialchars(file_get_contents($viewFile)));
            $this->tpl->assign('viewFile', $viewFile);
            return json_encode(array('success' => true, 'html' => $this->view()));
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Không tìm thấy portlet'));
    }

    /**
     * Get portlet's cp button
     */
    public function getPortletBtnAjax()
    {
        if(isset($this->params['pid']) && isset($this->params['type']))
        {
            switch($this->params['type'])
            {
                case 'menu':
                    //Get portlet info
                    $item = Models_MenuPortlet::getPortletInfo($this->params['pid']);
                    //get max position
                    $pMax = Models_MenuPortlet::getMaxOrderNoByRegion($item['menu_id'], $item['container_id']);
                    //return html button
                    $html = PortletHelper::getPortletCPButton($this->params['pid'], $pMax, $item['orderno'], $this->params['type'], $item['edit_view']);
                    break;
                case 'router':
                    //Get portlet info
                    $item = Models_RouterPortlet::getPortletInfo($this->params['pid']);
                    //get max position
                    $pMax = Models_RouterPortlet::getMaxOrderNoByRegion($item['router_id'], $item['container_id']);
                    //return html button
                    $html = PortletHelper::getPortletCPButton($this->params['pid'], $pMax, $item['orderno'], $this->params['type'], $item['edit_view']);
                    break;
            }
            if(isset($html))
                return json_encode(array('success' => true, 'htmlBtn' => $html));
        }
        return json_encode(array('success' => false));
    }
}