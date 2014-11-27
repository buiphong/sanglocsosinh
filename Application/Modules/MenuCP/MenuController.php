<?php
class MenuController extends Controller
{
    public $arrTarget = array('_self' => 'Default','_blank' => 'Tab mới (_blank)');
	public function __init()
	{
		$this->checkPermission();
        $this->loadModule('PortletCP');
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * danh sách menu
	 */
	public function listAction()
	{
        if(empty($this->params['type']))
        {
            //get default type
            $type = Models_MenuType::getType();
        }
        else
            $type = Models_MenuType::getById($this->params['type']);

        //get menu multilevel
        $menus = Models_Menu::getMenuMultiLevel($type['id'], @$_SESSION['sys_langcode']);
        $this->tpl->assign('listMenu', $this->html->renderAction('getNestableHtml', array('menus' => $menus)));
        $this->tpl->assign('menuType', $this->html->renderAction('sidebarMenuType'));

		$this->tpl->assign('type', $type['id']);
        $this->tpl->assign('title', $type['type_name']);
		return $this->view();
	}

    public function listAjax()
    {
        $pageSize = @$this->params["iDisplayLength"];
        $offset = @$this->params["iDisplayStart"];

        $model = new Models_Menu();
        //language
        if(!empty($_SESSION['sys_langcode']))
            $model->db->where('lang_code', $_SESSION['sys_langcode']);
        if (!empty($this->params['type']))
            $model->db->where('type_id', $this->params['type']);

        if (!empty($this->params['parentid']))
            $model->db->where('parentid', $this->params['parentid']);
        else
            $model->db->where('(parentid=0 or isnull(parentid) = 1)');
        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('orderno');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('id,title,template,orderno,layout,type_id')->limit($pageSize,$offset)->getFieldsArray();
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
	
	/**
	 * Thêm mới menu
	 */
	public function createAjax()
	{	
		$this->setView('edit');
		$listTemplates = $this->getTemplates();		
		$this->tpl->assign('template', $this->html->genSelect('template', $listTemplates, '', 'name', 'name', array('style' => 'width: 300px', 'class' => 'field')));
		
		$this->tpl->assign('form_action', $this->url->action('createPost', array('type'=>$this->params['type'])));
		//type_id
		if(!empty($this->params['type']))
		{
			$this->tpl->assign('type_id', $this->params['type']);
			$this->tpl->assign('listLink', $this->url->action('list',array('type'=>$this->params['type'])));
			$modelType = new Models_MenuType($this->params['type']);
			$this->tpl->assign('MenuType', $modelType->type_name);
		}
		//layout
		$keys = array_keys($listTemplates);
		$layouts = $this->getLayout($keys[0]);

        $modelMenu = new Models_Menu();
        //Parent menu
        $menus = $modelMenu->getTreeMenu(@$this->params['type'], 0,true, @$_SESSION['sys_langcode']);
        $this->tpl->assign('parent_id', $this->html->genSelect('parentid',$menus, @$this->params['parentid']));
		
        //Lấy danh sách portlet
        $modelPortlet = new Models_Portlet();
        $this->tpl->assign('portlet', $this->html->genSelect('portlet_id', $modelPortlet->getArrPortlet(), $modelMenu->portlet_id,
        		'id', 'title',array('class' => 'chosen-select', 'style' => 'width: 300px'), 'None', true));
        
		$this->tpl->assign('layout', $this->html->genSelect('layout', $layouts, '', '', '', array('style' => 'width: 300px', 'class' => 'field')));
		$modelMenu->type_id = $this->params['type'];

        //Lấy link
        $linkType = Config::getConfig('MenuCP:linkType');
        foreach($linkType as $key=>$val)
        {
            if(isset($val['module']) && !Helper::moduleExist($val['module']))
                unset($linkType[$key]);
        }
        $this->tpl->assign('link_type', $this->html->genSelect('link_type', $linkType, '', 'code', 'name'));

		$this->tpl->assign('cmbTarget', $this->html->genSelect('target', $this->arrTarget));
		$this->unloadLayout();
		return $this->view($modelMenu);
	}
	
	public function createPostAjax(Models_Menu $model)
	{
		$model->type_id = $this->params['type_id'];
        $model->url_title = String::seo($model->title);
        if(!empty($_SESSION['sys_langcode']))
            $model->lang_code = $_SESSION['sys_langcode'];
        //externallink
        $menu['link_type'] = $model->link_type;
        $menu['link_type_value'] = $model->link_type_value;
        $menu['title'] = $model->title;
        $model->externallink = $this->getLinkMenu($menu);
		if ($model->Insert()){
            //Update path
            if(!empty($model->parentid))
            {
                $path = $model->db->select('path')->where('id', $model->parentid)->getField();
                $path .= '/' . $model->db->InsertId();
            }
            else
                $path = $model->db->InsertId();
            $model->db->where('id', $model->db->InsertId())->update(array('path' => $path));

            $menus = Models_Menu::getMenuMultiLevel($this->params['type_id']);
			return json_encode(array('success' => true,'html' => $this->html->renderAction('getNestableHtml',
                    array('menus' => $menus)), 'type' => 'create', 'continue' => true, 'nestableReload' => true,
                    'msg' => 'Thêm mới menu thành công'));
		}
		else 
		    return json_encode(array('success' => false, 'msg' => $this->model->error));
	}
	
	/**
	 * Sửa menu
	 */
	public function editAjax()
	{
		$modelMenu = new Models_Menu($this->params['id']);
		if (!empty($this->params['id']))
		{
			$listTemplates = $this->getTemplates();
			if ($modelMenu)
			{
				if (!empty($modelMenu->template))
					$layouts = $this->getLayout($modelMenu->template);
			}
				
			$this->tpl->assign('template', $this->html->genSelect('template', $listTemplates, $modelMenu->template, 'name', 'name', array('style' => 'width: 300px', 'class' => 'field'), '----', true));
		}
		//layout
		$layouts = $this->getLayout($modelMenu->template);

        //Parent menu
        $menus = $modelMenu->getTreeMenu($modelMenu->type_id, 0,true, @$_SESSION['sys_langcode']);
        $this->tpl->assign('parent_id', $this->html->genSelect('parentid',$menus, $modelMenu->parentid));
		//Lấy danh sách portlet
		$modelPortlet = new Models_Portlet();
		$this->tpl->assign('portlet', $this->html->genSelect('portlet_id', $modelPortlet->getArrPortlet(), $modelMenu->portlet_id,
									'id', 'title',array('class' => 'chosen-select', 'style' => 'width: 300px'), 'None', true));
        //Lấy link
        $linkType = Config::getConfig('MenuCP:linkType');
        foreach($linkType as $key=>$val)
        {
            if(isset($val['module']) && !Helper::moduleExist($val['module']))
                unset($linkType[$key]);
        }
        $this->tpl->assign('link_type', $this->html->genSelect('link_type', $linkType, $modelMenu->link_type, 'code', 'name'));
		$this->tpl->assign('form_action', $this->url->action('editPost', array('type'=>$modelMenu->type_id)));
		$this->tpl->assign('layout', $this->html->genSelect('layout', $layouts, $modelMenu->layout, '', '', array('style' => 'width: 300px', 'class' => 'field'), '-----', true));
		$this->tpl->assign('menuTypeLink', $this->url->action('list', array('type' => $modelMenu->type_id)));
        $this->tpl->assign('cmbTarget', $this->html->genSelect('target', $this->arrTarget, $modelMenu->target));
		$modelType = new Models_MenuType($modelMenu->type_id);
		$this->tpl->assign('MenuType', $modelType->type_name);
		$this->unloadLayout();
		return $this->view($modelMenu);		
	}
	
	public function editPostAjax(Models_Menu $model)
	{
        if(empty($model->portlet_id))
            $model->portlet_id = 0;
	    $model->lang_code = @$_SESSION['sys_langcode'];
        $model->url_title = String::seo($model->title);
        //externallink
        $menu['link_type'] = $model->link_type;
        $menu['link_type_value'] = $model->link_type_value;
        $menu['title'] = $model->title;
        $model->externallink = $this->getLinkMenu($menu);
		if ($model->Update()){
            //Update path
            if(!empty($model->parentid))
            {
                $path = $model->db->select('path')->where('id', $model->parentid)->getField();
                $path .= '/' . $model->id;
            }
            else
                $path = $model->id;
            $model->db->where('id', $model->id)->update(array('path' => $path));

			return json_encode(array('success' => true, 'nestable' => true, 'id' => $model->id,
                'str' => $model->title, 'type' => 'edit'));
		}
		else
			return json_encode(array('success' => false, 'msg' => $model->error));
	}
	
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$model = new Models_Menu();
        $modelMP = new Models_MenuPortlet();
		$ids = $this->params['id'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->Delete("id=$id"))
					return json_encode(array('success' => false, 'msg' => $model->db->error));
                else
                    $modelMP->db->where('menu_id', $id)->Delete();

		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $model->db->error));
            else
                $modelMP->db->where('menu_id', $ids)->Delete();
		}
		return json_encode(array('success' => true, 'nestable' => true, 'id' => $ids));
	}

    /**
     * update position
     */
    public function updatePositionAjax()
    {
        if($this->params['data'])
        {
            $result = $this->_updatePosArray($this->params['data']);
            if($result)
                return json_encode(array('success' =>  true));
        }
        return json_encode(array('success' => false, 'msg' => $result));
    }

    private function _updatePosArray($items = array(), $parent = 0)
    {
        $result = false;
        if($items)
        {
            foreach($items as $pos => $item)
            {
                if(($pos+1) != $item['orderno'] || $parent != $item['parent'])
                   $result = $this->_updatePosition($item['id'], $pos+1, $parent);
                if(isset($item['children']))
                   $result = $this->_updatePosArray($item['children'], $item['id']);
            }
        }
        return $result;
    }

    private function _updatePosition($menuId, $position, $parentId)
    {
        return Models_Menu::updatePosMenu($menuId, $position, $parentId);
    }

    /**
	 * get nestable menu view
	 */
	public function getNestableHtmlAction()
	{
        if($this->params['menus'])
        {
            foreach($this->params['menus'] as $menu)
            {
                $child = '';
                if(!empty($menu['subs']))
                    $child = $this->html->renderAction('_getNestableHtml', array('menus' => $menu['subs']));
                $this->tpl->assign('child',$child);
                $this->tpl->insert_loop('main.menu', 'menu', $menu['values']);
            }
        }
        $this->unloadLayout();
		return $this->view();
	}
	
	public function _getNestableHtmlAction()
	{
        if($this->params['menus'])
        {
            foreach($this->params['menus'] as $menu)
            {
                $child = '';
                if(!empty($menu['subs']))
                    $child = $this->html->renderAction('_getNestableHtml', array('menus' => $menu['subs']));
                $this->tpl->assign('child',$child);
                $this->tpl->insert_loop('main.menu', 'menu', $menu['values']);
            }
        }
        $this->unloadLayout();
		return $this->view();
	}

    //get link type value
    function getLinkTypeValueAjax()
    {
        $linkType = Config::getConfig('MenuCP:linkType');
        if ($this->params['typeCode'] == 'link')
            return json_encode(array('success' => true, 'html' => '<input class="field" type="text" name="link_type_value" id="link_type_value" size=40 value="'.$this->params['value'].'">'));
        $type = $linkType[$this->params['typeCode']];
        $html = $this->renderAction(array($type['action'], $type['controller'], $type['module']), array('value' => @$this->params['value']));
        return json_encode(array('success' => true, 'html' => $html));
    }
	
	/**
	 * Lấy danh sách template 
	 * @return array
	 */
	function getTemplates()
	{
		$ignoredDir = array('.', '..');
		$listDir = PTDirectory::getSubDirectories((Url::getAppDir().'Templates'));
		foreach ($listDir as $key => $dir)
		{
			$listDir[$key] = array('name' => $key, 'path' => $dir);
		}
		return $listDir;
	}
	
	/**
	 * lấy danh sách layout
	 */
	function getLayout($template)
	{
        if(!empty($template) && is_dir(Url::getAppDir().'Templates/' . $template))
        {
            $list = @scandir(Url::getAppDir().'Templates/' . $template . '/layout');
            $ignoredItem = array('.', '..','.svn');
            $arrItem = array();
            if(is_array($list))
            {
                foreach ($list as $item)
                {
                    if (!(array_search($item, $ignoredItem) > -1))
                    {
                        $item = substr($item, 0, -4);
                        $arrItem[$item] = $item;
                    }
                }
            }
            return $arrItem;
        }
        return false;
	}
	
	public function getLayoutAjax()
	{
		if (!empty($this->params['template']))
		{
			$layout = $this->getLayout($this->params['template']);
			if ($layout)
				return json_encode(array('success' => true, 'html' => $this->html->genSelect('layout', $layout, '', '', '', array('style' => 'width: 300px', 'class' => 'field'))));
			else
				return json_encode(array('success' => false, 'msg' => 'Không tìm thấy layout',
                    'html' => $this->html->genSelect('layout', array(), '', '', '', array('style' => 'width: 300px', 'class' => 'field'),'----', true)));
		}
		return json_encode(array('success' => false, 'msg' => 'Không tìm thấy template'));
	}
	//Tạo externallink
	public function getLinkMenu($menu)
	{
		$url = $menu['link_type_value'];
		switch ($menu['link_type'])
		{
			case 'link':
				$url = $menu['link_type_value'];
				break;
			case 'ncatid':
                $this->loadModule('NewsCP');
                $url = NewsHelper::getLinkCat($menu['link_type_value']);
				break;
			case 'newsid':
                $this->loadModule('NewsCP');
				$url = NewsHelper::getLinkNews($menu['link_type_value']);
				break;
			case 'pcatid':
                $this->loadModule('ProductCP');
				$model = new Models_ProductCategory();
				$arrCat = $model->getArrayParent($menu['link_type_value']);
				$strCat = '';
	            if(!empty($arrCat))
	            {
	                foreach($arrCat as $vl)
	                   $strCat .= String::seo($vl['title']) . '/';
	                $strCat  = substr($strCat , 0, -1);
	            }		
				$url = $this->url->action('getListProduct', 'ProductComponent', 'Product', array('catname' => String::seo($strCat), 'catid' => $menu['link_type_value']));
				break;
			case 'productid':
                $this->loadModule('ProductCP');
				$model = new Models_Products();
				$modelCat = new Models_ProductCategory();
				$product = $model->db->select('name,category_id')->where('id',$menu['link_type_value'])->getFields();
				
				$arrCat = $modelCat->getArrayParent($product['category_id']);
				$strCat = '';
	            if(!empty($arrCat))
	            {
	                foreach($arrCat as $vl)
	                   $strCat .= String::seo($vl['title']) . '/';
	                $strCat  = substr($strCat , 0, -1);
	            }		            
				$url = $this->url->action('detailProduct', 'ProductComponent', 'Product', array('catname'=>$strCat,'name' => String::seo($product['name']), 'id' => $menu['link_type_value']));
				break;
            case 'eccatid':
                $this->loadModule('ECProductCP');
                $url = ECProductHelper::getCatLink($menu['link_type_value']);
                break;
		}
		return $url;
	}

    public function sidebarMenuTypeAjax()
    {
        return $this->html->renderAction('sidebarMenuType');
    }

    /**
     * sidebar news category
     */
    public function sidebarMenuTypeAction()
    {
        $menuTypes  = Models_MenuType::getMenuTypeMultiLevel(0, @$_SESSION['sys_langcode']);
        if($menuTypes)
            $this->tpl->assign('html', $this->html->renderAction('_childSidebarMenuType', array('types' => $menuTypes, 'selected' => @$this->params['id'])));
        $this->tpl->assign('listLink', $this->url->action('list'));

        $this->unloadLayout();
        return $this->view();
    }

    public function _childSidebarMenuTypeAction()
    {
        foreach ($this->params['types'] as $type)
        {
            if (!empty($type['subs']))
            {
                $this->tpl->assign('child', $this->html->renderAction('_childSidebarMenuType',
                    array('types' => $type['subs'], 'selected' => $this->params['selected'])));
            }
            else
                $this->tpl->assign('child', '');
            if ($type['id'] == $this->params['selected']) {
                $cat['class'] = 'current';
            }
            else
                $type['class'] = '';
            $type['link'] = $this->url->action('index', array('type'=>$type['id']));
            $this->tpl->insert_loop('main.type', 'type', $type);
        }
        $this->unloadLayout();
        return $this->view();
    }

    /**
     * Load link type
     */
    public function loadLinkType()
    {
        $type = Config::getConfig('MenuCP:linkType');
        foreach($type as $k => $v)
        {
            //check module
        }
    }

    public function updatePathAction()
    {
        $model = new Models_Menu();
        $menus = $model->db->select('id,title,parentid,path')->orderby('parentid')->getFieldsArray();
        foreach($menus as $menu)
        {
            if(empty($menu['path']))
                $menu['path'] = $menu['id'];
            $path = '';
            if(!empty($menu['parentid']))
                $path = $model->db->select('path')->where('id', $menu['parentid'])->getField();
            if($path && strpos($menu['path'], $path) === false)
                $path .= '/' . $menu['path'];
            else
                $path = $menu['path'];
            $model->db->where('id', $menu['id'])->update(array('path' => $path));
        }
        echo 'Done!';
    }
}








