<?php
/**
 * Rewrite url
 * @author buiphong
 *
 */
class Rewrite
{
	/**
	 * Router hiện tại
	 * @var Router
	 */
	protected $_router;
	
	/**
	 * Router trong file rewrite
	 */
	protected $_routers;

    protected $fileName = 'Rewrite.php';
	
	public $file = 'rewrite.xml';
	
	/**
	 * request uri
	 */
	protected $_requestUri;
	
	/**
	 * Mảng tham số
	 */
	protected $_args;
	
	/**
	 * Khởi tạo rewrite
	 * @param unknown_type $router
	 */
	public function __construct($router)
	{
		$this->_router = $router;
		//Lấy cấu hình rewrite, các tham số cơ bản: request uri
		//Kiểm tra file rewrite
		if (!is_file($this->fileName))
			throw new Exception('Không tìm thấy tệp tin cấu hình rewrite: ' . $this->fileName . ' trong hệ thống');
		else
		{
			$this->getRouters();
			$uri = $_SERVER['REQUEST_URI'];
			$basePath = explode(DIRECTORY_SEPARATOR, ROOT_PATH);
				
			$uri = explode('/', $uri);
			$uri = array_slice($uri, count($basePath), count($uri) - count($basePath));
			
			$this->_requestUri = implode('/', $uri);
		}
	}
	
	/**
	 * Xử lý phân tích rewrite
	 */
	public function processing()
	{
		//Request hệ thống
		$uri = new Uri();
		$arrUri = $uri->parseRequestUri();
		$request = implode('/', $arrUri['request']);
		foreach ($this->_routers as $regex => $router)
		{
			if ($regex)
			{
				//So sánh request và router
				if ($this->compareRouter($regex, $request))
				{
                    if (!empty($router['alias']))
                        $this->_router->alias = $router['alias'];
                    else
                        $this->_router->alias = 'Portal';

					if (!empty($router['module']))
						$this->_router->module = $router['module'];
					else
						$this->_router->module = 'index';
					
					if (!empty($router['controller']))
						$this->_router->controller = $router['controller'];
					else
						$this->_router->controller = 'Index';
					
					if (!empty($router['action']))
						$this->_router->action = $router['action'];
					else
						$this->_router->controller = 'index';
					$this->_router->args = $this->parseParams($router['url']);
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Lấy đường dẫn đã rewrite
	 */
	public function getUrl()
	{
        $t = count($this->_router->args);
		foreach ($this->_routers as $regex => $router)
		{
            if(MULTI_LANGUAGE && !empty($router['langcode']) && $router['langcode'] != $_SESSION['langcode'])
                continue;
            if ($this->_router->module) {
                if (!isset($router['module']) || $router['module'] != $this->_router->module)
                    continue;
            }
            if ($this->_router->controller) {
                if (!isset($router['controller']) ||
                        $router['controller'] != $this->_router->controller
                )
                    continue;
            } else
                continue;
            if ($this->_router->action) {
                if (!isset($router['action']) ||
                        $router['action'] != $this->_router->action
                )
                    continue;
            } else
                continue;
            //Link được định nghĩa trong rewrite
            $url = $router['url'];
            //Kiểm tra args
            if ($t != $router['totalParams'])
                continue;
            else
            {
                //Kiểm tra các tham số có tương ứng hay không
                $v = true;
                $aks = array_keys($router['args']);
                foreach ($this->_router->args as $k => $e)
                {
                    if (!in_array($k, $aks)) {
                        $v = false;
                    }
                }
                if (!$v) {
                    continue;
                }
            }
            //Replace các tham số thành các giá trị tương ứng
            foreach ($this->_router->args as $n => $v) {
                if(!is_array($v))
                    $url = str_replace("{" . $n . "}", $v, $url);
            }
            require_once 'Url.php';
            return Url::getApplicationUrl() . '/' . $url;
		}
		return null;
	}
	
	/**
	 * so sánh request hiện tại và router
	 */
	private function compareRouter($router_preg, $request)
	{
		$router_preg = trim($router_preg);
		$request = trim($request);
		$out = array();
		$x = preg_match("/^$router_preg/i", $request, $out);
		for ($i = 1; $i < sizeof($out); $i++) {
			$this->_args[] = $out[$i];
		}
		return $x;
	}
	
	/**
	 * Phân tích url lấy tham số truyền vào
	 */
	private function parseParams($url_rewrite)
	{
		$total = array();
		$out = array();
		$prg = '/\{(\w+)\}/';
		while (preg_match($prg, $url_rewrite, $out)) {
            $total[] = $out[1];
			$url_rewrite = str_replace($out[1], "", $url_rewrite);
		}
		$args = array();
        $m = count($total);
		for ($i = 0; $i < $m; $i++) {
			if (isset($this->_args[$i]))
				$args[$total[$i]] = $this->_args[$i];
			else
				$args[$total[$i]] = null;
		}
		$this->_args = $args;
		return $this->_args;
	}

    /**
     * Load danh sách router được cấu hình trong rewrite
     */
    private function getRouters()
    {
        //Kiểm tra xem đã có trong runtime hay không
        if(!file_exists(Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Routers.php') || DEBUG)
        {
            //Load default rewrite
            $arr = include(ROOT_PATH . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . $this->fileName);
            foreach ($arr as $k => $v)
            {
                //Count total params
                $v['args'] = $this->parseParams($v['url']);
                $v['totalParams'] = count($v['args']);
                $this->_routers[$k] = $v;
            }
            //Xử lý cho file rewrite các module
            $subdirs = PTDirectory::getSubDirectories(
                APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Modules');
            $arr2 = PTDirectory::getSubDirectories(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Portal');
            $subdirs = array_merge($subdirs, $arr2);
            $tmpArr = array();
            $i = 1;
            foreach ($subdirs as $fname => $fpath)
            {
                if(file_exists($fpath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $this->fileName))
                {
                    $a = include($fpath . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $this->fileName);
                    $tmpArr[$a['priority'] . $i] = $a['items'];
                    $i++;
                }
            }
            //order $tmpArr
            krsort($tmpArr);
            //Load menu's rewrite
            if($tmpArr)
            {
                foreach($tmpArr as $items)
                    foreach($items as $k => $v)
                    {
                        //Count total params
                        $v['args'] = $this->parseParams($v['url']);
                        $v['totalParams'] = count($v['args']);
                        $this->_routers[$k] = $v;
                    }
            }
            //Save to file
            if(!DEBUG)
                file_put_contents(Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Routers.php', '<?php $routers = ' . var_export($this->_routers,true) . '; ?>');
        }
        else
        {
            require Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Routers.php';
            $this->_routers = $routers;
        }
        return true;
    }
}
