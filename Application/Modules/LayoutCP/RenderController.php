<?php
/**
 * Lớp render layout, cho phép chỉnh sửa, thêm portlet
 * @author buiphong
 *
 */
class RenderController extends Presentation
{
	public function __init()
	{
		//$this->checkPermission();
        $this->loadModule(array('MenuCP'));
	}
	
	public function indexAction()
	{
        //get current router from uri
        $router = new Router();
        $router->analysisRequestUri();
        PTDirectory::emptyDir(Url::getAppDir() . 'Cache' . DIRECTORY_SEPARATOR . 'Adodb', false);
		$modelMenu = new Models_Menu();
        $currObj = '';
        $currRouter = array();
		//kiểm tra menu id
		if (!empty($router->args['menu']) && $router->module == 'Index' && $router->controller == 'Index')
        {
        	//get menu
            $m = explode('/', $router->args['menu']);
        	$m = end($m);
			$menu = $modelMenu->db->where('url_title', $m)->getfields();
            $mId = $menu['id'];
            $this->menuId = $mId;
			$this->menuPortlet = $menu['portlet_id'];
            $this->layout = $menu['layout'];
            $this->template = $menu['template'];
            $currObj = 'Menu: ' . $menu['title'];
            $currRouter = array('type' => 'menu', 'value' => $router->args['menu']);
		}
		elseif (!empty($router->module) && !empty($router->controller) && !empty($router->action))
		{
			//render action
            $currObj = 'Router: ' . $router->action;
            $currRouter = array('type' => 'router', 'value' => array(
                'module' => $router->module,
                'controller' => $router->controller,
                'action' => $router->action,
                'router_id' => ''
            ));
            //get template, layout router
            $modelRouter = new Models_Router();
            $pRouter = $modelRouter->getRouter($router);
            $arrSK = array();
            if($pRouter)
            {
                $this->tpl->parse('main.editRouter');
                //get cmb params
                $arrSelect = unserialize($pRouter['args']);
                if($arrSelect)
                    $arrSK = array_keys($arrSelect);
                $currRouter['router_id'] = $pRouter['id'];
                $currRouter['args'] = @$pRouter['args'];
            }
            else
                $this->tpl->parse('main.addRouter');

            if($router->args)
            {
                $num = 1;
                foreach($router->args as $k => $v)
                {
                    $check = '';
                    if(in_array($k, $arrSK) && $v == $arrSelect[$k])
                        $check = 'checked';
                    $this->tpl->insert_loop('main.updateLayout.currParams.param', 'param', array('key' => $k, 'value' => $v, 'checked' => $check, 'num' => $num));
                    $num++;
                }
                $this->tpl->parse('main.updateLayout.currParams');
            }

            $modelPortlet = new Models_Portlet();
            if(!empty($pRouter['template']))
                $this->template = $pRouter['template'];
            else
            {
                $this->loadModule('TemplateCP');
                //load default
                $modelTemp = new Models_Template();
                $defaultTemp =  $modelTemp->getDefault();
                if($defaultTemp)
                {
                    $this->template = $modelTemp->getDefault();
                }
            }
            if(!empty($pRouter['layout']))
                $this->layout = $pRouter['layout'];
            elseif($this->template)
            {
                //Load default
                $this->layout = 'index';
                $modelPortlet->db->where('id', $pRouter['id'])->update(array('layout' => 'index'));
            }
            //skin portlet
            //Lấy danh sách skin
            $skins = PTDirectory::getFilesDir(SKIN_DIR);
            if($skins)
            {
                $this->tpl->assign('cmbSkin', $this->html->genSelect('CP-cmbSkin', $skins, $pRouter['skin'], '', '',
                    array('class' => ''), 'Default skin', true));
                $this->tpl->parse('main.updateLayout.boxSkin');
            }
		}
		else
		{
            if(MULTI_LANGUAGE && $_SESSION['langcode'])
                $modelMenu->db->where('lang_code', $_SESSION['langcode']);
			//hiển thị menu mặc định
			$menu = $modelMenu->db->where('parentid <=', 0)->where('is_default', 1)->where('orderno >=', 0)->orderby('orderno')->getFields();
			$this->menuId = $mId = $menu['id'];
            if (!$menu)
            {
                throw new Exception($modelMenu->error . '<br>' . "select id,title,isinhome,orderno,parentid,template,layout from menu
					where id=".$menu['id']);
            }
            $this->layout = $menu['layout'];
            $this->template = $menu['template'];
		}

        require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'PortletCP' . DIRECTORY_SEPARATOR . 'PortletController.php';

        $this->tpl->assign('layout', $this->layout);
        //Load template, default layout
        $this->tpl->assign('template', $this->template);
        $listLayout = PortletController::getLayout($this->template);
        $this->tpl->assign('cmbLayout', $this->html->genSelect('CP-cmbLayout', $listLayout, $this->layout, null,null,array('class' => ''), '-------', true));

        //Load combobox template, layout
        $listDir = PortletController::getTemplates();
        $this->tpl->assign('cmbTemplate', $this->html->genSelect('CP-cmbTemplate', $listDir, $this->template, 'name', 'name',array('class' => ''), '-------', true));
        $this->tpl->assign('object', $currObj);
        $this->tpl->assign('currRouter', base64_encode(serialize($currRouter)));
        //edit layout
        if(isset($_SESSION['pt_control_panel']['editLayout']) && $_SESSION['pt_control_panel']['editLayout'])
        {
            $this->tpl->assign('ELChecked', 'checked');
            $this->tpl->parse('main.updateLayout');
        }
        else
            $this->tpl->assign('ELChecked', '');
        $this->tpl->assign('frmUpdateLayout', $this->url->action('updateLayout'));
        $this->tpl->assign('content', @$this->params['content']);
        $this->tpl->assign('currentUri', Url::getContentUrl($_SERVER['REQUEST_URI']));
		return $this->view();
	}

    public function getLayoutAjax()
    {
        require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'PortletCP' . DIRECTORY_SEPARATOR . 'PortletController.php';
        if (!empty($this->params['template']))
        {
            $layout = PortletController::getLayout($this->params['template']);
            if ($layout)
                return json_encode(array('success' => true, 'html' => $this->html->genSelect('CP-cmbLayout', $layout, null,null,array('class' => 'chosen-select'), '-------', true)));
            else
                return json_encode(array('success' => true, 'html' => $this->html->genSelect('CP-cmbLayout', array(), null,null,array('class' => 'chosen-select'), '-------', true)));
        }
        return json_encode(array('success' => true, 'html' => $this->html->genSelect('CP-cmbLayout', array(), null,null,array('class' => 'chosen-select'), '-------', true)));
    }

    public function updateLayoutAction()
    {

    }

    public function updateLayoutPost()
    {
        $result = $this->_updateLayout($this->params);
        if($result)
        {
            header('location: ' . $this->params['currUri']);
        }
        else
            return false;
    }

    /**
     * update layout current object
     */
    public function updateLayoutAjax()
    {
        $result = $this->_updateLayout($this->params);
        if($result)
            return json_encode($result);
        else
            return json_encode(array('success' => false));
    }

    /**
     * Thực hiện update layout
     */
    function _updateLayout($params = array())
    {
        $router = unserialize(base64_decode($params['currRouter']));
        if(!empty($params['params']))
            ksort($params['params']);
        if(isset($router['type']) && $router['type'] == 'router')
        {
            $r = $router['value'];
            $model  = new Models_Router();
            if(empty($router['router_id']) || (isset($params['params']) && $router['args'] != serialize($params['params'])))
            {
                //create portlet
                $data = array(
                    'module' => $r['module'],
                    'controller' => $r['controller'],
                    'action' => $r['action'],
                    'layout' => $params['CP-cmbLayout'],
                    'template' => $params['CP-cmbTemplate'],
                    'skin' => @$params['CP-cmbSkin'],
                    'create_time' => date('Y-m-d H:i:s')
                );
                if(isset($params['params']) && !empty($params['params']))
                    $data['args'] = serialize($params['params']);
                if($model->Insert($data))
                    return array('success' => true);
                else
                    return array('success' => false, 'msg' => $model->db->error);
            }
            else
            {
                //update
                if($model->db->where('id', $router['router_id'])->update(array(
                    'template' => $params['CP-cmbTemplate'], 'layout' => $params['CP-cmbLayout'],
                    'skin' => @$params['CP-cmbSkin'])))
                    return array('success' => true);
                else
                    return array('success' => false, 'msg' => $model->db->error);
            }
        }
        elseif(isset($router['type']) && $router['type'] == 'menu')
        {
            $model = new Models_Menu();
            $menu = $model->db->select('id,layout,template')->where('url_title', $router['value'])->getFields();
            if($menu)
            {
                if($menu['template'] != $params['CP-cmbTemplate'] || $menu['layout'] != $params['CP-cmbLayout'])
                {
                    if($model->db->where('id', $menu['id'])->update(array('template' => $params['CP-cmbTemplate'], 'layout' => $params['CP-cmbLayout'])))
                        return array('success' => true);
                    else
                        return array('success' => false, 'msg' => $model->db->error);
                }
            }
        }
        else
        {
            //Create router index for current langcode
            $model  = new Models_Router();
            //create portlet
            $data = array(
                'module' => 'Index',
                'controller' => 'Index',
                'action' => 'index',
                'layout' => $params['CP-cmbLayout'],
                'template' => $params['CP-cmbTemplate'],
                'skin' => @$params['CP-cmbSkin'],
                'create_time' => date('Y-m-d H:i:s'),
                'lang_code' => @$_SESSION['langcode']
            );
            if(isset($params['params']) && !empty($params['params']))
                $data['args'] = serialize($params['params']);
            if($model->Insert($data))
                return array('success' => true);
            else
                return array('success' => false, 'msg' => $model->db->error);
        }
    }

    /**
     * enable/disable edit layout
     */
    public function enEditLayoutAjax()
    {
        if($this->params['val'] == '1')
            //Thiết lập session enable editLayout
            $_SESSION['pt_control_panel']['editLayout'] = true;
        else
            $_SESSION['pt_control_panel']['editLayout'] = false;
        return json_encode(array('success' => true));
    }
}