<?php
/**
 * Class router phân tích uri
 * @author buiphong
 */
class Router
{
	/**
	 * Mảng lưu trữ  request uri
	 * @var array
	 */
	private $_requestUri;

    /**
     * Id router
     * @var int
     */
    public $id;

    /**
     * Alias
     * @var string
     */
    public $alias;

	/**
	 * Tên area
	 * @var string
	*/
	public $module;
	/**
	 * Tên controller
	 * @var string
	 */
	public $controller;
	/**
	 * Tên action
	 * @var string
	 */
	public $action;
	/**
	 * Tham số truyền vào từ QueryString
	 * @var array
	 */
	public $args = array();

    /**
     * Tham số escaped_fragment cho các ajax link
     */
    public $escaped_fragment;

    /**
	 * Tham số cho rest
	 */
	public $restArgs = array();

    /**
     * Header response code
     */
    public $headerResponseCode = 200;

    /**
	 * Khởi tạo các giá trị cho Router
	*/
	public function __construct()
	{
		//Nhận toàn bộ modules và đường dẫn
		$this->_detectModuleDefined();
		//Nhận request uri
		//$this->analysisRequestUri();
	}
	/**
	 * Gán một thể hiện của Router
	 * @param $router
	 * @return XPHP_Router
	 */
	public function setInstance($router)
	{
		
		if (is_array($router)) {
            if (isset($router['alias']))
                $this->action = $router['alias'];
			if (isset($router['action']))
				$this->action = $router['action'];
			if (isset($router['controller']))
				$this->controller = $router['controller'];
			if (isset($router['module']))
				$this->module = $router['module'];
			if (isset($router['args']))
				$this->args = $router['args'];
			return $this;
		}
		else if (get_class($router) == "Router")
		{
			$this->action = $router->action;
			$this->controller = $router->controller;
			$this->module = $router->module;
			$this->args = $router->args;
            $this->alias = $router->alias;
		}
	}
	/**
	 * Kiểm tra, phân tích request uri lấy ra Area, Controller, Action, Param (Thông tin router)
	 * @param void
	 * @return array Trả về mảng lưu trữ thông tin của router
	 */
	public function analysisRequestUri()
	{
		require_once ROOT_PATH . '/Library/Uri.php';
		$uri = new Uri();
        $r = $uri->getRequestUri();
        //Check runtime
        if(file_exists(Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'RequestUri.php'))
            require Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'RequestUri.php';
        //Lấy giá trị escaped_fragment
        if(isset($_GET['_escaped_fragment_']))
            $this->escaped_fragment = $_GET['_escaped_fragment_'];
        if(isset($arrayUri) && isset($arrayUri[$r]) && !DEBUG)
        {
            $this->alias = $arrayUri[$r]['alias'];
            $this->module = $arrayUri[$r]['module'];
            $this->controller = $arrayUri[$r]['controller'];
            $this->action = $arrayUri[$r]['action'];
        }
        else
        {
            $arrUri = $uri->parseRequestUri();
            if (!empty($arrUri['request'][0]))
            {
                $arrAction = $arrUri['request'];
                //Lấy cấu hình rewrite
                $rewrite = new Rewrite($this);
                //Kiểm tra rewrite trước
                if (!$rewrite->processing())
                {
                    if ($arrAction[0] == '')
                        unset($arrAction[0]);
                    foreach ($arrAction as $param)
                    {
                        if (!$this->module && $this->_detectModule($param))
                        {
                            array_shift($arrAction);
                            continue;
                        }
                        if (!$this->controller && $this->_detectController($param))
                        {
                            array_shift($arrAction);
                            continue;
                        }
                        if (!$this->action && $this->_detectAction($param))
                        {
                            array_shift($arrAction);
                            break;
                        }
                    }

                    $this->restArgs = $arrAction;
                    if(!empty($arrUri['params']))
                        $arrParams = explode('&', $arrUri['params']);
                    if (!empty($arrParams))
                    {
                        $arr = array();
                        foreach ($arrParams as $param)
                        {
                            $a = explode('=', $param);
                            if (isset($a[1]) && $a[1] != '')
                            {
                                $arr[$a[0]] = urldecode($a[1]);
                            }
                        }
                        if (!empty($arr))
                            $this->args = $arr;
                    }
                }
                if(!$this->alias || !$this->module || !$this->controller || !$this->action)
                {
                    //Get config router
                    $config = Config::getConfig('error_router');
                    $config = $config['404'];
                    if(!empty($config))
                    {
                        $this->alias = $config['alias'];
                        $this->module = $config['module'];
                        $this->controller = $config['controller'];
                        $this->action = $config['action'];
                        $this->headerResponseCode = 404;
                    }
                }
            }
            else
            {
                $default = PTRegistry::get('defaultRouter');
                if(empty($default))
                {
                    $default['alias'] = 'Portal';
                    $default['module'] = 'Index';
                    $default['controller'] = 'Index';
                    $default['action'] = 'index';
                }
                if (!$this->alias)
                    $this->alias = $default['alias'];
                if (!$this->module)
                    $this->module = $default['module'];
                if (!$this->controller)
                    $this->controller = $default['controller'];
                if (!$this->action)
                    $this->action = $default['action'];
                if(!isset($arrayUri))
                    $arrayUri = array();
                $arrayUri[$r] = array(
                    'alias' => $this->alias,
                    'module' => $this->module,
                    'controller' => $this->controller,
                    'action' => $this->action
                );
            }
            //write to file
            if(!DEBUG)
                file_put_contents(Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'RequestUri.php', '<?php $arrayUri = ' . var_export($arrayUri,true) . '; ?>');
        }
	}
	
	/**
	 * Nhận diện toàn bộ modules có trong thư mục Modules và đường dẫn của nó
	 * @return array $moduleName => $modulePath
	 */
	private function _detectModuleDefined()
	{
        if(!file_exists(Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Modules.php') || DEBUG)
        {
            require_once ROOT_PATH . DIRECTORY_SEPARATOR .'Library/PTDirectory.php';
            //portal
            $subdirs = PTDirectory::getSubDirectories(
                            APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Portal');
            foreach ($subdirs as $fname => $fpath)
                $modules[$fname] = array('name' => $fname,
                        'path' => $fpath, 'alias' => 'Portal');
            //Modules
            $subdirs = PTDirectory::getSubDirectories(
                    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Modules');
            foreach ($subdirs as $fname => $fpath)
                $modules[$fname] = array('name' => $fname,
                        'path' => $fpath, 'alias' => 'Modules');
            //Write runtime file
            file_put_contents(Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Modules.php', '<?php $modules = ' . var_export($modules,true) . '; ?>');
        }
	}
	/**
	 * Kiểm tra xem tham số truyền vào có phải module hay không ?
	 * @param string Chuỗi cần kiểm tra
	 * @return boolean
	 */
	private function _detectModule($part)
	{
        require Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Modules.php';
		if(isset($modules[$part]))
        {
            $this->module = $modules[$part]['name'];
            $this->alias = $modules[$part]['alias'];
        }
		return isset($modules[$part]);
	}
	/**
	 * Kiểm tra xem tham số truyền vào có phải controller hay không ?
	 * @param string Chuỗi cần kiểm tra
	 * @return boolean
	 */
	private function _detectController($part)
	{
        $controllerFolder = '';
		//Lấy ra đường dẫn tới thư mục Controllers
		if ($this->module)
			$controllerFolder = "{$this->getModulePath($this->module)}/";
        if(!$controllerFolder)
            return false;
		//Lấy toàn bộ danh sách các file trong thư mục Controllers
		$phpFiles = glob($controllerFolder . "*.php");
        $controllerName = '';
		//Kiểm tra từng file trong Controllers
		foreach ($phpFiles as $f)
		{
			$f = str_replace(array("/", "\\"), "/", $f);
			$f = substr($f, strrpos($f, "/") + 1);
			if(strtolower($f) == strtolower("{$part}Controller.php"))
			{
				preg_match('/(\w+)Controller.php/i', $f, $matches);
				$controllerName = $matches[1];
			}
		}
		//Trả về false nếu không tìm thấy controller
		if(!$controllerName)
			return false;
		//Gán kết quả vè trả về true
		$this->controller = $controllerName;
		return true;
	}
	/**
	 * Kiểm tra xem tham số truyền vào có phải action hay không ?
	 * @param string Chuỗi cần kiểm tra
	 * @return boolean
	 */
	private function _detectAction($part)
	{
        $controllerFolder = '';
		//Lấy ra đường dẫn tới thư mục Controllers
		if ($this->module)
			$controllerFolder = "{$this->getModulePath($this->module)}/";
        if(!$controllerFolder)
            return false;
		//Include thêm đường dẫn tới thư mục chứa controller
		set_include_path($controllerFolder . PATH_SEPARATOR . get_include_path());
	
		if ($this->controller)
		{
			require_once "{$this->controller}Controller.php";
		}
		else
			return false;
		$class = "{$this->controller}Controller";

		$actionName = false;
		$methods = get_class_methods($class);
		//Nhận action
        if($methods)
        {
            foreach ($methods as $m){
                //Kiểm tra xem có một trong những thể hiện của action hay không ?
                if (preg_match('/^'.$part.'(Action|Ajax|Post|Get)/i', $m))
                {
                    $actionName = preg_replace("/(Action|Ajax|Post|Get)/", "", $m, 1);
                }
            }
        }
		//Trả về false nếu không tìm thấy action
		if(!$actionName)
			return false;
        //Gán kết quả vè trả về true
	    $this->action = $part;

	    return true;
	}
	
	/**
	* Lấy ra đường dẫn của một module
	* @param string $moduleName Tên module cần lấy đường dẫn
	* @return string
	 */
	 public static function getModulePath($moduleName)
	 {
        require Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Modules.php';
		foreach ($modules as $module)
		{
		    if($moduleName == $module['name'])
				return $module['path'];
		}
		return false;
	}
	/**
	* Lấy ra thể hiện của của Router
	* @return XPHP_Router
	*/
	public function getInstant()
	{
		return clone $this;
	}
	
	/**
	* Kiểm tra xem router hiện tại có rỗng không
	*/
	public function isEmpty()
	{
		return empty($this->action)&& empty($this->controller)&& empty($this->module);
	}
	
	/**
	* Xóa toàn bộ các thông tin action, controller, module detect được
	* @return bool
	*/
	public function emptyRouter()
	{
        $this->action = null;
        $this->controller = null;
        $this->module = null;
	}
	
	public function getRouterString()
	{
		if($this->module)
			$rt = array($this->module, $this->controller, $this->action);
			else
				$rt = array($this->controller, $this->action);
				return implode("_", $rt);
	}
}