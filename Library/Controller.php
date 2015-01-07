<?php
/**
 * Class Controller
 * @author buiphong
 */
class Controller extends Controller_Abstract
{
    var $layout;
    var $layoutPath;
    var $paramLayout;
    var $template;

    /**
     * Đối tượng hỗ trợ url
     */
    public $url;

    /**
     * Các tham số được truyền vào
     */
    public $params;

    /**
     * Page title
     * @var string
     */
    public $title;

    /**
     * @var XTemplate
     */
    public $tpl;

    public $html;


    /**
     * Khởi tạo controller
     * @param Router $router || null
     */
    public function __construct(&$router=null)
    {
        PTRegistry::set('CacheTime', 0);
        parent::__construct($router);
        //Lưu các tham số được truyền vào
        $this->url = new Url($this->router);
        $this->url->mode = 'view';
        $this->html = new Html($this->router);
        //lấy ra file view của action
        $viewFile = $this->url->getUrlContent('Application'.DIRECTORY_SEPARATOR.'Modules' . DIRECTORY_SEPARATOR .
        $this->router->module . DIRECTORY_SEPARATOR . 'forms'. DIRECTORY_SEPARATOR . $this->router->controller .
        DIRECTORY_SEPARATOR . $this->router->action . '.htm');
        //Khai báo template
        $this->tpl = new XTemplate($viewFile);
        //Include Path
        $this->setIncludePath();
        $this->getParams();
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
        $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . PATH_SEPARATOR . 'Modules' . PATH_SEPARATOR . $folder;
        set_include_path($dirs . PATH_SEPARATOR . $incPath);
    }

    /**
     * Chạy Front Controller
     */
    public function dispatch ($router = null, $method = '', $viewCPMode = 'view', $viewMode='full')
    {
        if (empty($router))
            $router = $this->router;
        //Xử lý Controller
        $class = "{$router->controller}Controller";
        $cf = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR.'Modules'. DIRECTORY_SEPARATOR .
            $router->module . DIRECTORY_SEPARATOR . $class . '.php';
        if(!file_exists($cf))
            throw new Exception('Không tìm thấy file: ' . $class . '.php tại "' . 'Application'.DIRECTORY_SEPARATOR.'Modules'. DIRECTORY_SEPARATOR .$router->module . '"');
        //Gọi tới action lấy giá trị trả về XPHP_View_Result_Interface
        require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR.'Modules'. DIRECTORY_SEPARATOR .
            $router->module . DIRECTORY_SEPARATOR . $class . '.php';

        if (!class_exists($class))
            throw new Exception('Không tìm thấy class: ' . $class);
        $obj = new $class($router);

        $obj->viewMode = $viewMode;
        //Gọi phương thức khởi tạo
        if (method_exists($obj, '__init'))
            $obj->__init();
        $incPath = get_include_path();
        $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $router->module;
        set_include_path($incPath . PATH_SEPARATOR . $dirs);
        if ($method != '')
        {
            $result = $this->_callAction($obj, "{$router->action}$method");
        }
        else
        {
            /*
             * Gọi đến action đặc biệt
            */
            //Nếu request là ajax request thì gọi tới actionAjax và trả về kết quả
            if ($this->isAjaxRequest())
            {
                //Xử lý gọi actionAjax
                $result = $this->_callAction($obj, "{$router->action}Ajax");
            }
            else
            {
                //Nếu người dùng sử dụng form POST dữ liệu thì gọi tới actionPost trước khi gọi action
                if (sizeof($_POST) > 0) {
                    //Xử lý gọi actionPost
                    $result = $this->_callAction($obj, "{$router->action}Post");
                }
                //Nếu người dùng sử dụng form GET để gửi dữ liệu thì gọi tới actionGet trước khi gọi action
                //if (sizeof($_GET) > 1) {
                //Xử lý gọi actionPost
                //	$result = $this->_callAction($obj, "{$this->router->action}Get");
                //}
                //Nếu POST và GET không trả về View_Result => Gọi action
                if (!isset($result)) {
                    //Xử lý gọi action
                    $result = $this->_callAction($obj, "{$router->action}Action");
                }
            }
        }
        /*
         * XỬ LÝ KẾT QUẢ TRẢ VỀ
        */
        return $result;
    }

    function _callAction($obj, $action)
    {
        if (!method_exists($obj, $action))
            throw new Exception('Không tìm thấy phương thức: ' . $action);

        $model = $this->_getModel($obj, $action);
        if ($model)
        {
            $result = $obj->$action($model);
        }
        else
        {
            $result = $obj->$action();
        }
        return $result;
    }

    public function view(PTModel $model = null)
    {
        if ($model != null)
        {
            $modelProperties = $model->getModelProperties();
            foreach ($modelProperties as $property)
            {
                $modelValue[$property] = $model->$property;
            }
            $this->tpl->assign('model', $modelValue);
        }
        if(!empty($this->template) && empty($this->layoutPath))
            $this->loadLayout('index');
        if (!empty($this->layoutPath))
        {
            $tpl = new XTemplate($this->layoutPath);
            //tự động load template, file view
            $this->tpl->combineJs();
            $this->tpl->combineCss();
            $this->tpl->parse('main');
            $tpl->assign('content', $this->tpl->text('main'));
            $tpl->parse('main.content');
            //Read xml render action to view
            if (!empty($this->paramLayout))
            {
                //render action
                foreach ($this->paramLayout as $key => $values)
                {
                    if (!empty($values) && is_array($values))
                    {
                        $content = $this->renderAction(array($values['action'], $values['controller'], $values['module'], @$values['params']));
                        $tpl->assign($key, $content);
                    }
                }
                $tpl->assign('title', $this->title);
            }
            $tpl->assign('appPath', Url::getApplicationUrl());
            $tpl->combineJs();
            $tpl->combineCss();
            $tpl->parse('main');
            return $tpl->text('main');
        }
        else
        {
            //Kiểm tra có file view hay không
            if (!is_file($this->tpl->filename))
            {
                throw new Exception('Không tìm thấy file view: ' . $this->tpl->filename);
            }
            //tự động load template, file view
            $this->tpl->combineJs();
            $this->tpl->combineCss();
            $this->tpl->parse('main');
            return $this->tpl->text('main');
        }
    }

    function loadLayout($layout)
    {
        $this->layoutPath = $this->url->getUrlContent('Templates/' . $this->template . '/layout/' . $layout . '.htm');
        if (!is_file($this->layoutPath))
            throw new Exception('Không tìm thấy layout tại: ' . $this->layoutPath);
        //get render action
        $this->analyseLayout($layout);
    }

    function loadTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Thiết lập file view
     */
    function setView($name)
    {
        $viewFile = $this->url->getUrlContent('Application/Modules/' . $this->router->module . '/forms/' .
        $this->router->controller . '/' . $name . '.htm');
        //Khai báo template
        $this->tpl = new XTemplate($viewFile);
    }

    /**
     * Load skin
     */
    function loadSkin($name)
    {
        $viewFile = $this->url->getUrlContent('skins/' . $name . '.htm');
        if (is_file($viewFile)) {
            $this->tpl = new XTemplate($viewFile);
        }
    }

    /**
     * Thiết lập tiêu đề trang
     */
    function setTitle($title = '')
    {
        if (!empty($title))
            $title .= ' | ';
        $this->title = $title;
    }

    function analyseLayout($layout)
    {
        //Phân tích layout
        if(file_exists($this->url->getUrlContent('Templates/' . $this->template . '/xml/' . $layout . '.xml')))
        {
            $arrXml = Xml::toArray($this->url->getUrlContent('Templates/' . $this->template . '/xml/' . $layout . '.xml'));
            $this->paramLayout = $arrXml;
        }
    }

    function unloadLayout()
    {
        $this->layout = '';
        $this->layoutPath = '';
        $this->template = '';
    }

    public function isAjaxRequest()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            return true;
        else
            return false;
    }

    /**
     * Render action
     */
    function renderAction($params = array())
    {
        $value = '';
        $module = $params[1];
        $arrParram = array();
        if (is_array($params[2]))
        {
            $arrParram = $params[2];
            $module = $params[1];
        }
        elseif (!empty($params[3]))
        {
            $arrParram = $params[3];
            $module = $params[2];
        }
        elseif (!is_array($params[2]))
            $module = $params[2];

        $class = $params[1];
        $action = $params[0];
        $router = new Router();
        $router->alias = 'Modules';
        $router->module = $module;
        $router->controller = $class;
        $router->action = $action;
        $router->args = $arrParram;
        return $this->dispatch($router, 'Action');
    }

    /**
     * Render CP action
     */
    function renderCPAction($params = array(), $viewMode = 'full')
    {
        $value = '';
        $module = $params[1];
        $arrParram = array();
        if (is_array($params[2]))
        {
            $arrParram = $params[2];
            $module = $params[1];
        }
        elseif (!empty($params[3]))
        {
            $arrParram = $params[3];
            $module = $params[2];
        }
        elseif (!is_array($params[2]))
            $module = $params[2];

        $class = $params[1];
        $action = $params[0];
        $router = new Router();
        $router->module = $module;
        $router->controller = $class;
        $router->action = $action;
        $router->args = $arrParram;
        return $this->dispatch($router, 'Action', 'cp', $viewMode);
    }

    /**
     * Phương thức kiểm tra quyền của người dùng
     */
    function checkPermission(Router $router = null)
    {
        if (empty($router)) {
            $router = $this->router;
        }
        #--- now check
        $cur_page = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //check admin
        if (isset($_SESSION[Config::getConfig('session_admin')]) && Config::getConfig('session_admin_value') == $_SESSION['pt_control_panel'][Config::getConfig('session_admin')])
            $is_admin = true;
        else
            $is_admin = false;
        $cnf_loginUrl = Config::getConfig('login_page');
        $loginUrl = $this->url->action($cnf_loginUrl[0], $cnf_loginUrl[1], $cnf_loginUrl[2], array('url' => base64_encode($cur_page)));
        if (intval(Config::getConfig('has_security'))>0){
            if ($cur_page!=$loginUrl){
                if (empty($_SESSION['pt_control_panel'][Config::getConfig('session_user')])){
                    $_SESSION["current_page"] = $_SERVER['PHP_SELF'];
                    header("Location: ".$loginUrl);
                }
            }//
        }//end if has security

        if (!$is_admin)
        {
            $hasPermission = $this->html->renderAction('checkPermission', 'Permission', 'Permission', array('router' => $router));
            if (!$hasPermission){
                if ($this->isAjaxRequest())
                {
                    echo 'Bạn không có quyền thực hiện chức năng này!';
                    die;
                }
                else
                {
                    echo "Permission denied !";
                    //redirect
                    $url = Url::getApplicationUrl();
                    header( "refresh:2;url=$url" );
                    die;
                }
            }
        }
    }

    public function showError($code, $desc)
    {
        $this->url->redirectAction('error', 'Index', 'ControlPanel', array('code' => $code, 'desc' => $desc,
            'url' => $this->url->getActionUrl($this->router)));
    }

    /**
     * Phương thức phân tích, lấy ra model
     * @param Controller $controller
     * @param string $action
     * @return PTModel | boolean
     */
    private function _getModel (&$controller, $action)
    {
        //Lấy ra toàn bộ các params phương thức $action của $controller
        $refMethod = new ReflectionMethod($controller, $action);
        $params = $refMethod->getParameters();
        if (!empty($params))
        {
            foreach ($params as $param)
            {
                //Lấy ra lớp của param
                if ($param->getClass())
                {
                    $modelClass = $param->getClass()->getName();
                    $obj = new $modelClass();
                    if ($obj instanceof PTModel)
                    {
                        $controller->setModel($obj);
                        $controller->parseModelParams();
                        $model = &$controller->getModel();
                        return $model;
                    }
                }
            }
        }
        return false;
    }
}
