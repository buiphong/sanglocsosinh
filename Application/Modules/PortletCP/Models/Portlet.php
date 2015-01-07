<?php
#[Table('portlets')]
#[PrimaryKey('id')]
class Models_Portlet extends PTModel
{
	public $id;

	public $title;

	public $userid;

	public $languageid;
	
	public $skin;
	
	public $desc;
	
	public $values;
	
	public $module;
	
	public $args;
	
	public $controller;
	
	public $action;
	
	public $group_id;
	
	#[DataType('number')]
	public $edit_view;
	
	public $url;
	
	public $type;
	
	public $template;
	
	public $layout;
	
	/**
	 * Lấy danh sách portlet
	 */
	public function getArrPortlet()
	{
		$arrPortlet = $this->db->select('id,title')->orderby('title')->getAll();
		return $arrPortlet;
	}

    /**
     * @param $router Router
     */
    public function getTemplateRouter($router)
    {
        if ($router)
        {
            //Get with params
            $portlets = $this->db->where('module', $router->module)->where('controller', $router->controller)
                ->where('action', $router->action)->getcFieldsArray();
            $tP = count($portlets);
            if($tP > 1)
            {
                $select = reset($portlets);
                //order portlet
                for($i = 0; $i < $tP; $i++)
                {
                    if(!empty($portlets[$i]['args']))
                    {
                        $t1 = unserialize($portlets[$i]['args']);
                        $t1 = count($t1);
                    }
                    else
                        $t1 = 0;
                    for($j = $i+1; $j < $tP; $j++)
                    {
                        if(!empty($portlets[$j]['args']))
                        {
                            $t2 = unserialize($portlets[$j]['args']);
                            $t2 = count($t2);
                        }
                        else
                            $t2 = 0;
                        if($t2 > $t1)
                        {
                            $tmp = $portlets[$i];
                            $portlets[$i] = $portlets[$j];
                            $portlets[$j] = $tmp;
                        }
                    }
                }
                $tmp = end($portlets);
                foreach($portlets as $p)
                {
                    $args = unserialize($p['args']);
                    if($args)
                    {
                        ksort($router->args);
                        if(count($args) <= count($router->args))
                        {
                            $valid = true;
                            foreach($args as $k => $v)
                            {
                                if(!isset($router->args[$k]) || $router->args[$k] != $v)
                                    $valid = false;
                            }
                            if($valid)
                            {
                                $tmp = $p;
                                break;
                            }
                        }
                    }
                }
                $portlets = $tmp;
            }
            else
            {
                $portlets = reset($portlets);
                //check args
                if(!empty($portlets['args']))
                {
                    $arr = unserialize($portlets['args']);
                    foreach($arr as $k => $v)
                    {
                        $k = str_replace("'", '', $k);
                        if(!isset($router->args[$k]) || $router->args[$k] != $v)
                        {
                            return false;
                        }
                    }
                }
            }
            return $portlets;
        }
        return false;
    }

    /**
     * Get portlets by menu
     * @return array portlet group by region
     */
    public static function getPortletByMenu($menuId)
    {
        if(!empty($menuId))
        {
            $obj = self::getInstance();
            if(MULTI_LANGUAGE && $_SESSION['langcode'])
                $obj->db->where('menu_portlet.lang_code', $_SESSION['langcode']);
            $arrPortlet = $obj->db->select('portlets.title,portlets.module,portlets.edit_view,portlets.controller,
                portlets.action,portlets.id as pid,portlets.values as pvalues,menu_portlet.skin,portlets.type,menu_portlet.orderno,
                menu_portlet.id,menu_portlet.params,menu_portlet.values,menu_portlet.cache_time,menu_portlet.container_id')
                ->join('menu_portlet', 'menu_portlet.portlet_id=portlets.id')
                ->where('menu_portlet.menu_id', $menuId)->orderby('orderno')->getcFieldsArray();
            $data = array();
            if($arrPortlet)
            {
                foreach($arrPortlet as $key => $portlet)
                {
                    $data[$portlet['container_id']][$key] = $portlet;
                }
            }
            return $data;
        }
        return false;
    }

    function getPortletRegionMenu($menuId, $region)
    {
        if (!empty($region) && !empty($menuId))
        {
            if(MULTI_LANGUAGE && $_SESSION['langcode'])
                $this->db->where('menu_portlet.lang_code', $_SESSION['langcode']);
            $arrPortlet = $this->db->select('portlets.title,portlets.module,portlets.edit_view,portlets.controller,portlets.action,portlets.id as pid,portlets.values as pvalues,menu_portlet.skin,portlets.type,menu_portlet.id,menu_portlet.params,menu_portlet.values,menu_portlet.cache_time')
                ->join('menu_portlet', 'menu_portlet.portlet_id=portlets.id')
                ->where('menu_portlet.menu_id', $menuId)->where('menu_portlet.container_id',$region)
                ->orderby('orderno')->getcFieldsArray();
            if ($arrPortlet)
                return $arrPortlet;
            else
            {
                $modelMP = new Models_MenuPortlet();
                //select default portlet
                $modelMenu = new Models_Menu($menuId);
                if(MULTI_LANGUAGE && $_SESSION['langcode'])
                    $this->db->where('default_layout_portlet.lang_code', $_SESSION['langcode']);
                $arrPortlet = $this->db->select('portlets.title,portlets.module,portlets.edit_view,portlets.controller,portlets.action,portlets.id as pid,portlets.values as pvalues,portlets.type,default_layout_portlet.id,default_layout_portlet.params,default_layout_portlet.values')
                    ->join('default_layout_portlet', 'default_layout_portlet.portlet_id=portlets.id')
                    ->where('default_layout_portlet.template', $modelMenu->template)
                    ->where('default_layout_portlet.layout',$modelMenu->layout)
                    ->where('default_layout_portlet.region',$region)
                    ->orderby('orderno')->getcFieldsArray();
                //Add to menu portlet
                $i = 1;
                foreach ($arrPortlet as $k => $p)
                {
                    $data = array(
                        'menu_id' => @$this->params['menuId'],
                        'portlet_id' => @$p['pid'],
                        'container_id' => @$this->params['region'],
                        'title' => $p['title'],
                        'params' => $p['params'],
                        'values' => $p['pvalues'],
                        'type' => $p['type'],
                        'orderno' => $i
                    );
                    if(MULTI_LANGUAGE && $_SESSION['langcode'])
                        $data['lang_code'] = $_SESSION['langcode'];
                    if ($modelMP->Insert($data))
                        $arrPortlet[$k]['id'] = $modelMP->db->InsertId();
                    $i++;
                }
                return $arrPortlet;
            }
        }
        else
            return false;
    }

    public static function getPortletByRouter($routerId)
    {
        if (!empty($routerId))
        {
            $obj = self::getInstance();
            if(MULTI_LANGUAGE && $_SESSION['langcode'])
                $obj->db->where('router_portlet.lang_code', $_SESSION['langcode']);
            $arrPortlet = $obj->db->select('portlets.title,portlets.module,portlets.edit_view,portlets.controller,
            portlets.action,portlets.id as pid,portlets.values as pvalues,portlets.type,router_portlet.skin,router_portlet.orderno,
            router_portlet.id,router_portlet.params,router_portlet.values,router_portlet.cache_time,router_portlet.container_id')
                ->join('router_portlet', 'router_portlet.portlet_id=portlets.id')
                ->where('router_portlet.router_id', $routerId)
                ->orderby('router_portlet.orderno')->getcFieldsArray();
            $data = array();
            if($arrPortlet)
            {
                foreach($arrPortlet as $key => $portlet)
                {
                    $data[$portlet['container_id']][$key] = $portlet;
                }
            }
            return $data;
        }
        return false;
    }

    function getPortletRegionRouter($routerId, $region)
    {
        if (!empty($region) && !empty($routerId))
        {
            if(MULTI_LANGUAGE && $_SESSION['langcode'])
                $this->db->where('router_portlet.lang_code', $_SESSION['langcode']);
            $arrPortlet = $this->db->select('portlets.title,portlets.module,portlets.edit_view,portlets.controller,portlets.action,portlets.id as pid,portlets.values as pvalues,portlets.type,router_portlet.skin,router_portlet.id,router_portlet.params,router_portlet.values,router_portlet.cache_time')
                ->join('router_portlet', 'router_portlet.portlet_id=portlets.id')
                ->where('router_portlet.router_id', $routerId)
                ->where('router_portlet.container_id', $region)->orderby('router_portlet.orderno')->getcFieldsArray();
            if ($arrPortlet)
                return $arrPortlet;
            else
            {
                //Check template - layout portlet
                if (!empty($portlet['template']) && !empty($portlet['layout'])) {
                    //select default portlet
                    if(MULTI_LANGUAGE && $_SESSION['langcode'])
                        $this->db->where('default_layout_portlet.lang_code', $_SESSION['langcode']);
                    $arrPortlet = $this->db->select("portlets.title,portlets.module,portlets.edit_view,
					        portlets.controller,portlets.action,portlets.id as pid,portlets.values as pvalues,
							portlets.type,default_layout_portlet.id,default_layout_portlet.params,default_layout_portlet.values")
                        ->join('default_layout_portlet', 'default_layout_portlet.portlet_id=portlets.id')
                        ->where('default_layout_portlet.template', $portlet['template'])
                        ->where('default_layout_portlet.layout', $portlet['layout'])
                        ->where('default_layout_portlet.region', $portlet['layout'])
                        ->orderby('orderno')->getcFieldsArray();
                    $modelRP = new Models_RouterPortlet();
                    $i = 1;
                    foreach ($arrPortlet as $k => $p)
                    {
                        $data = array(
                            'router_id' => $routerId,
                            'portlet_id' => $p['pid'],
                            'container_id' => $region,
                            'title' => $p['title'],
                            'params' => $p['params'],
                            'values' => $p['pvalues'],
                            'type' => $p['type'],
                            'orderno' => $i
                        );
                        if(MULTI_LANGUAGE && $_SESSION['langcode'])
                            $data['lang_code'] = $_SESSION['langcode'];
                        if ($modelRP->Insert($data))
                            $arrPortlet[$k]['id'] = $modelRP->db->InsertId();
                        $i++;
                    }
                    return $arrPortlet;
                }
            }
        }
        else
            return false;
    }

    public function getCustomPortlet($region)
    {
        $arrData = $this->db->select('url,values')->where('type', 'custom_portlet')
            ->where('url', $region)->getcFieldsArray();
        return $arrData;
    }

    public function getPortletMenu($menuId)
    {
        $model = new Models_Menu($menuId);
        if (!empty($model->portlet_id))
        {
            $modelPortlet = new Models_Portlet($model->portlet_id);
            return $modelPortlet;
        }
        return false;
    }
}