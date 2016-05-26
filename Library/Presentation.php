<?php
/**
 * Lớp Presentation - hỗ trợ portal - portlet
 * @author buiphong
 *
 */
class Presentation extends Controller_Abstract
{
	public $layout;
	
	public $template;
	
	public $menu;
	
	public $menuPortlet;
	
	public $layoutPath;
	
	public $viewMode = 'full'; //'single: không load layout
	
	protected $_params;
	
	protected $ignoredParams = array('{appPath}');
	
	protected $specialParams = array('{title}','{description}','{keywords}','{css}','{javascript}','{headerSeo}','{ga}','{imgAvata}','{loadCss}','{loadScript}');
	
	public $viewParam;
	
	/**
	 * Lớp html hệ thống
	 * @var Html
	 */
	public $html;
	
	/**
	 * Lớp url hệ thống
	 * @var Url
	 */
	public $url;
	
	/**
	 * Xtemplate
	 * @var XTemplate
	 */
	public $tpl;
	
	/**
	 * Ngôn ngữ
	 * @var unknown
	 */
	public $language;
	
	function __construct(&$router)
	{
        if(DEBUG)
            PTRegistry::set('CacheTime', 0);
        else
            PTRegistry::set('CacheTime', CACHETIME);
		parent::__construct($router);
		$this->html = new Html($this->router);
		$this->url = new Url($this->router);
		//Khai báo template
		$this->tpl = new XTemplate($this->url->getUrlContent('Application'.DIRECTORY_SEPARATOR.$this->router->alias. DIRECTORY_SEPARATOR .
						$this->router->module . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR .
						$this->router->controller . DIRECTORY_SEPARATOR . $this->router->action . '.htm'));

        if(MULTI_LANGUAGE)
        {
            //Load language
            $this->language = PTLanguage::getResource();
        }
        $this->setIncludePath();
        $this->viewParam = new stdClass();
        //load module
        $this->loadModule(array('ConfigCP', 'PortletCP'));
	}
	
	public function view()
	{
        //Load template
		if($this->viewMode == 'full')
        {
            //Kiểm tra template, layout
            if (!empty($this->menu))
            {
                $this->template = $this->menu['template'];
                $this->layout = $this->menu['layout'];
            }
            else
            {
                $model = new Models_Router();
                $values = $model->getRouter($this->router);
                if ($values)
                {
                    $this->template = $values['template'];
                    $this->layout = $values['layout'];
                }
            }
            //current router
            if (!empty($this->layout))
            {
                if($this->loadLayout($this->layout))
                {
                    $tpl = new XTemplate($this->layoutPath);
                    //Load region
                    if (!empty($this->_params))
                    {
                        $paramLayout = array();
                        //Phân tích xml layout
                        if(file_exists($this->url->getUrlContent('Templates/' . $this->template . '/xml/' . $this->layout . '.xml')))
                            $paramLayout = Xml::toArray($this->url->getUrlContent('Templates/' . $this->template . '/xml/' . $this->layout . '.xml'));

                        //get array portlet. Group by region
                        $type = '';
                        if (!empty($this->menu))
                        {
                            $arrPortlet = Models_Portlet::getPortletByMenu($this->menu['id']);
                            $type = 'menu';
                        }
                        else
                        {
                            $arrPortlet = Models_Portlet::getPortletByRouter($values['id']);
                            $type = 'router';
                        }

                        foreach ($this->_params as $region)
                        {
                            $regionName = str_replace('{', '', $region);
                            $regionName = str_replace('}', '', $regionName);
                            if (in_array($region, $this->specialParams))
                            {
                                if (!empty($this->viewParam->$regionName))
                                {
                                    $tpl->assign($regionName, $this->viewParam->$regionName);
                                }
                                else
                                {
                                    //Load from website's config: title, desc, keywords, ga
                                    $confVal = Models_ConfigValue::getConfValue('conf_' . $regionName, @$_SESSION['langcode']);
                                    if (!empty($confVal)) {
                                        $tpl->assign($regionName, $confVal);
                                    }
                                }
                            }
                            else
                            {
                                $regionContent = '';
                                //Load content theo cấu hình trong file xml nếu có
                                if(!empty($paramLayout[$regionName]))
                                {
                                    $regionContent .= $this->html->renderAction($paramLayout[$regionName]['action'],
                                        $paramLayout[$regionName]['controller'], $paramLayout[$regionName]['module'], $paramLayout[$regionName]['alias']);
                                }
                                //region: content: load mặc định cho router hiện tại
                                $currView = '';
                                $arrView = array();
                                if ($region === '{content}')
                                {
                                    if (!empty($this->menu))
                                    {
                                        $model = new Models_Portlet();
                                        $portlet = $model->getPortletMenu($this->menu['id']);
                                        if (!empty($portlet->action))
                                            $regionContent .= PortletHelper::getPortletView($portlet);
                                    }
                                    //current router
                                    $this->tpl->combineJs();
                                    $this->tpl->combineCss();
                                    $this->tpl->parse('main');
                                    $currView = $this->tpl->text('main');
                                    $arrView[0] = $currView;
                                }
                                if (isset($arrPortlet[$region]))
                                {
                                    $pMax = count($arrPortlet[$region]);
                                    if($currView != '')
                                    {
                                        $pMax += 1;
                                        for($pNum = 1; $pNum < $pMax; $pNum++)
                                            $arrView[$pNum] = $currView;
                                    }
                                    $cacheDir = ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR . DIRECTORY_SEPARATOR;
                                    $pNum = 1;
                                    foreach ($arrPortlet[$region] as $portlet)
                                    {
                                        $cacheFile = base64_encode($portlet['id'] . '_'. $portlet['action']) . '.cache';
                                        //Check portlet cache
                                        if(!Helper::enableEditLayout() && !DEBUG && file_exists($cacheDir . $cacheFile) && (time() - filemtime($cacheDir . $cacheFile) < HTML_CACHETIME))
                                        {
                                            //Check file time
                                            $arrView[$portlet['orderno']] = file_get_contents($cacheDir . $cacheFile);
                                        }
                                        else
                                        {
                                            //delete cache file
                                            @unlink($cacheDir . $cacheFile);
                                            if ($portlet['type'] != 'custom_portlet' && !empty($portlet['action']))
                                            {
                                                if(!Helper::enableEditLayout())
                                                    $arrView[$portlet['orderno']] = PortletHelper::getPortletView($portlet);
                                                else
                                                    $arrView[$portlet['orderno']] = PortletHelper::getPortletCPButton($portlet['id'],$pNum,$pMax,$type, @$portlet['edit_view']) .
                                                        PortletHelper::getPortletView($portlet);
                                            }
                                            if(base64_decode($portlet['values'], true))
                                                $portlet['values'] = base64_decode($portlet['values']);
                                            if(!isset($arrView[$portlet['orderno']]))
                                                $arrView[$portlet['orderno']] = $portlet['values'];
                                            else
                                                $arrView[$portlet['orderno']] .= $portlet['values'];
                                            //Write cache file
                                            if(HTML_CACHETIME > 0)
                                                file_put_contents($cacheDir . $cacheFile, $arrView[$portlet['orderno']]);
                                        }
                                        $pNum ++;
                                    }
                                }
                                $regionContent .= implode('', $arrView);
                                $region = str_replace('{', '', $region);
                                $region = str_replace('}', '', $region);
                                if(!Helper::enableEditLayout())
                                    $tpl->assign($region, $regionContent);
                                else
                                    $tpl->assign($region, $this->loadCPRegion($regionContent, $region, @$this->menu['id']));
                            }
                        }
                    }
                    $tpl->combineJs();
                    $tpl->combineCss();
                    $tpl->parse('main');
                    if(Helper::adminLoggedIn())
                    {
                        //render CP view
                        return $this->html->renderAction('index', 'Render', 'LayoutCP', 'Modules', array('content' => $tpl->text('main')));
                    }
                    return $tpl->text('main');
                }
            }
        }

        if(!file_exists($this->tpl->filename))
            echo '<br>Không tìm thấy file view tại: ' . $this->router->alias . DIRECTORY_SEPARATOR . $this->router->module.
                DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . $this->router->controller . DIRECTORY_SEPARATOR .
                $this->router->action . '.htm';
        //return current action
        $this->tpl->combineJs();
        $this->tpl->combineCss();
        $this->tpl->parse('main');
        if(Helper::adminLoggedIn() && !Helper::isRenderLayout($this->router) && PTRegistry::get('currRouter') == $this->router )
        {
            //render CP view
            return $this->html->renderAction('index', 'Render', 'LayoutCP', 'Modules', array('content' => $this->tpl->text('main')));
        }
        else
            return $this->tpl->text('main');
	}
	
	function loadLayout($layout = 'index')
	{
		if (empty($this->template))
			$this->template = 'default';
        $this->layout = $layout;
		//Load layout
		$this->layoutPath = $this->url->getUrlContent(TEMPLATE_DIR . DIRECTORY_SEPARATOR . $this->template . DIRECTORY_SEPARATOR .
				'layout' . DIRECTORY_SEPARATOR . $layout . '.htm');
		if (!is_file($this->layoutPath))
		{
			throw new Exception('Không tìm thấy file layout: ' . $this->layoutPath);
            return false;
		}
		else
		{
			//phân tích lấy params
			$this->pre_analyseLayout();
		}
		return true;
	}
	
	function loadDefaulLayout()
	{
		$this->layout = 'index';
		$this->template = 'default';
	}
	
	/**
	 * Load cp content region
	 */
	function loadCPRegion($regionContent, $regionName, $menuId)
	{
        $r = new Router();
        $r->analysisRequestUri();
        //Get router_id
        $model = new Models_Router();
        $values = $model->getRouter($r);
	    $router = $r->module . '/' . $r->controller . '/' . $r->action;
		$html = '<div class="renderCP-region-border" id="region-'.$regionName.'"></div><div class="region-border-btn"><a href="#" class="cp-region-btn addPortlet" data-region="'.
			$regionName.'" data-menuid="'.$menuId.'" data-router-id="'.@$values['id'].'" data-router="'.$router.'" title="'.$regionName.'">Add portlet</a></div>';
		$html .= $regionContent;
		/* $html .= '<div class="cl"></div><div class="bottom-btn"><a href="#" class="cp-region-btn addPortlet" data-region="'.
			$regionName.'" data-menuid="'.$menuId.'" data-router="'.$router.'">Add portlet</a></div></div>'; */
		return $html;
	}
	
	/**
	 * Phân tích layout lấy params
	 */
	function pre_analyseLayout()
	{
		$content = file_get_contents($this->layoutPath);
		$exp = "/\{([a-zA-Z0-9]*)\}/";
		preg_match_all($exp, $content, $arr);
		if (!is_array($arr)) return false;
		if (count($arr[0])==0) return false;
		foreach ($arr[0] as $key=>$value)
		{
			if (in_array($value, $this->ignoredParams))
				unset($arr[0][$key]);
		}
		$this->_params = array_unique($arr[0]);
	}
	
	/**
	 * render action
	 * @param $params Tham số load action. Gồm: cpMode, skin
	 */
	function renderViewAction(Router $router, $params = array())
	{
		$controller = new Controller_Front($router);
		$params['loadTemplate'] = false;
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function getLayoutTemplateAction(Router $router)
	{
		require_once $this->url->getUrlContent('Application'.DIRECTORY_SEPARATOR. $router->alias .
				DIRECTORY_SEPARATOR . $router->module . DIRECTORY_SEPARATOR . $router->controller . '.php');
		$obj = new $router->controller();
		$action = $router->action . 'Action';
		$obj->$action();
		return array('template' => @$obj->template, 'layout' => @$obj->layout);
	}

    public function setViewMode($value = 'single')
    {
        $this->html->_loadTemplate = false;
        $this->viewMode = $value;
    }
	
	/**
	 * Thiết lập file view
	 */
	function setView($name, $skin=false)
	{
		if ($skin) {
            $viewFile = $this->url->getUrlContent('Skins' . DIRECTORY_SEPARATOR . $name);
		}
		else
			$viewFile = $this->url->getUrlContent('Application'.DIRECTORY_SEPARATOR. $this->router->alias . DIRECTORY_SEPARATOR .
					$this->router->module . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . $this->router->controller . DIRECTORY_SEPARATOR . $name . '.htm');
        if(is_file($viewFile))
        {
            $this->tpl->skin = $name;
            $this->tpl->changeFile($viewFile);
        }

	}

    /**
     * Load portlet skin
     */
    function loadSkinPortlet()
    {
        //Check for portlet setting
        $this->loadModule('PortletCP');
        $model = new Models_Portlet();
        $values = $model->getTemplateRouter($this->router);
        if(!empty($values['skin']))
            $this->setView($values['skin'], true);
    }

    /**
	 * Unload layout
	 */
	function unloadLayout()
	{
		$this->layout = '';
		$this->layoutPath = '';
		$this->template = '';
	}

    /**
     * Gán include path
     */
    public function setIncludePath()
    {
        //Lấy ra đường dẫn module sử dụng
        if(!empty($this->router->module))
            $folder = $this->router->module;
        else
            $folder = APPLICATION_PATH;
        //Set đường dẫn
        $incPath = get_include_path();
        $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . PATH_SEPARATOR . 'Portal' . PATH_SEPARATOR . $folder;
        set_include_path($dirs . PATH_SEPARATOR . $incPath);
    }
}