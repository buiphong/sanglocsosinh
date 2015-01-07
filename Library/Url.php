<?php
class Url
{
	private $router;

	var $rewriteEnable = true;
	
	public function __construct(Router $router = NULL)
	{
		if (!is_null($router))
			$this->router = $router;
	}

    /**
     * base64 encode image
     */
    public static function base64EncodeImage($url)
    {
        $e =  substr(strrchr($url,'.'),1);
        if ($url) {
            $imgbinary = fread(fopen($url, "r"), filesize($url));
            return 'data:image/' . $e . ';base64,' . base64_encode($imgbinary);
        }
    }

    /**
     * Get thumbnail using Image class
     * @param $path
     * @param $width
     * @param int $height
     * @param array $option
     */
    public static function getThumb($path, $width, $height=0, $option=array())
    {
        if(!empty($path))
        {
            $host = "http://{$_SERVER['HTTP_HOST']}";
            $photo_url = $path;
            $path_parts = pathinfo($photo_url);
            $filename = $path_parts['filename'];

            $photo_url = str_replace($host, '', $photo_url);
            $host = str_replace('www.', '', $host);
            $photo_url = str_replace($host, '', $photo_url);
            $photo_url = trim($photo_url, '/');
            $thumb = String::seo($path_parts['dirname'] . '/' . $path_parts['filename']);
            if(isset($path_parts['extension']))
                $thumb .= '.'.$path_parts['extension'];
            if(strtolower($path_parts['extension']) == 'bmp')
                return Url::getContentUrl($path);
            $crop = 'auto';
            if(!empty($option['crop']))
                $crop = $option['crop'];
            $thumburl = THUMBNAIL_DIR . DIRECTORY_SEPARATOR . "{$width}_{$height}";
            $thumburl .= '_' . $crop;
            $photo_url = ROOT_PATH . DIRECTORY_SEPARATOR . $photo_url;
            if(!is_file(ROOT_PATH . DIRECTORY_SEPARATOR . $thumburl . DIRECTORY_SEPARATOR . $thumb) && is_file($photo_url))
            {
                if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . $thumburl))
                    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . $thumburl);
                if(!is_file($thumburl . DIRECTORY_SEPARATOR . $thumb))
                {
                    $image = new Image($photo_url);
                    $image->resizeImage($width, $height, $crop);
                    if(!$image->save(Url::getAppDir() . $thumburl . DIRECTORY_SEPARATOR . $thumb, null, false))
                    {
                        return Url::getContentUrl($path);
                    }
                }
                //if(isset($option['encode']) && $option['encode'])
                //    return self::base64EncodeImage(Url::getAppDir() ."{$thumburl}".DIRECTORY_SEPARATOR."{$filename}");

                $thumburl = str_replace(DIRECTORY_SEPARATOR, '/', $thumburl);
                return Url::getContentUrl("/{$thumburl}/{$thumb}" );
            }
            elseif (is_file(ROOT_PATH . DIRECTORY_SEPARATOR . $thumburl.DIRECTORY_SEPARATOR.$thumb))
            {
                //if(isset($option['encode']) && $option['encode'])
                //    return self::base64EncodeImage(Url::getAppDir() ."{$thumburl}".DIRECTORY_SEPARATOR."{$filename}");
                $thumburl = str_replace(DIRECTORY_SEPARATOR, '/', $thumburl);
                return Url::getContentUrl("/{$thumburl}/{$thumb}" );
            }
            return Url::getContentUrl($path);
        }
    }

    /**
	 * Lấy đường dẫn thumbnail của ảnh
     * @param $option - array(crop => boolean, encode => boolean, waterMark => boolean)
	 */
	public static function thumbnail($path, $width, $height, $option = array())
	{
        if(!empty($path))
        {
            $host = "http://{$_SERVER['HTTP_HOST']}";
            $photo_url = $path;
            $photo_url = str_replace($host, '', $photo_url);
            $host = str_replace('www.', '', $host);
            $photo_url = str_replace($host, '', $photo_url);
            $photo_url = trim($photo_url, '/');
            $thumb = String::seo($photo_url);

            //Xu ly lay file name va duong dan
            $filename = explode('/', $photo_url);
            $filename = array_pop($filename);
            if (isset($option['crop']) && $option['crop'])
                $thumburl = THUMBNAIL_DIR . DIRECTORY_SEPARATOR . "{$width}_{$height}_crop";
            elseif(isset($option['waterMark']) && $option['waterMark'])
                $thumburl = THUMBNAIL_DIR . DIRECTORY_SEPARATOR . "{$width}_{$height}_fltr";
            else
                $thumburl = THUMBNAIL_DIR . DIRECTORY_SEPARATOR . "{$width}_{$height}";
            $photo_url = ROOT_PATH . DIRECTORY_SEPARATOR . $photo_url;
            if(!is_file(ROOT_PATH . DIRECTORY_SEPARATOR . "{$thumburl}/{$thumb}") && is_file($photo_url))
            {
                if(!is_file("{$thumburl}/{$thumb}"))
                {
                    if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . $thumburl))
                        PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . $thumburl);

                    //required phpThumb
                    require_once Url::getAppDir() . '/Packages/phpThumb/phpthumb.class.php';
                    $phpThumb = new phpthumb();
                    $phpThumb->setSourceFilename($photo_url);
                    if(!empty($width))
                        $phpThumb->setParameter('w', $width);
                    if(!empty($height))
                        $phpThumb->setParameter('h', $height);
                    $phpThumb->setParameter('q', 100);
                    //file type
                    $parts = explode('.', $filename);
                    $fileExt = strtolower($parts[(count($parts) - 1)]);
                    $phpThumb->setParameter('f', $fileExt);
                    if(isset($option['crop']) && $option['crop'])
                        $phpThumb->setParameter('zc', 1);
                    if(isset($option['waterMark']) && $option['waterMark'])
                        $phpThumb->setParameter('fltr', 'wmi|'.ROOT_PATH.DIRECTORY_SEPARATOR . 'Resources'.DIRECTORY_SEPARATOR.'water-mark.png|*|30');
                    if(isset($option['fltr']) && is_array($option['fltr']))
                        foreach($option['fltr'] as $opt)
                            $phpThumb->setParameter('fltr', $opt);

                    $phpThumb->setParameter('far','C');
                    $phpThumb->GenerateThumbnail();
                    if(!$phpThumb->RenderToFile(Url::getAppDir() . DIRECTORY_SEPARATOR ."{$thumburl}/{$thumb}"))
                    {
                        return Url::getContentUrl($path);
                    }
                }
                if(isset($option['encode']) && $option['encode'])
                    return self::base64EncodeImage(Url::getAppDir() . DIRECTORY_SEPARATOR."{$thumburl}".DIRECTORY_SEPARATOR."{$thumb}");
                else
                {
                    $thumburl = str_replace(DIRECTORY_SEPARATOR, '/', $thumburl);
                    return Url::getContentUrl("/{$thumburl}/{$thumb}" );
                }
            }
            elseif (is_file(ROOT_PATH . DIRECTORY_SEPARATOR . "{$thumburl}".DIRECTORY_SEPARATOR."{$thumb}"))
            {
                if(isset($option['encode']) && $option['encode'])
                    return self::base64EncodeImage(Url::getAppDir() . DIRECTORY_SEPARATOR."{$thumburl}".DIRECTORY_SEPARATOR."{$thumb}");
                else
                {
                    $thumburl = str_replace(DIRECTORY_SEPARATOR, '/', $thumburl);
                    return Url::getContentUrl("/{$thumburl}/{$thumb}" );
                }
            }
            return Url::getContentUrl($path);
        }
        else
            return false;
	}
	
	/**
	 * Lấy ra đường dẫn file
	 */
	public static function getContentUrl($content)
	{
		$url = "";
        if(strpos($content, 'http://') === false)
        {
            if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER["HTTPS"]) == "on")
                $url .= "https://" . $_SERVER['SERVER_NAME'];
            else
                $url .= "http://" . $_SERVER['SERVER_NAME'];
            if ($_SERVER['SERVER_PORT'] != '80')
                $url .= ':' . $_SERVER['SERVER_PORT'];
        }
		
		$dir = $url . self::getAbsoluteUrl() . $content;
		return $dir;
	}
	
	/**
	 * Lấy url ứng dụng
	 */
	public static function getApplicationUrl()
	{
		$url = "";
		if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER["HTTPS"]) == "on")
			$url .= "https://" . $_SERVER['SERVER_NAME'];
		else
			$url .= "http://" . $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != '80')
			$url .= ':' . $_SERVER['SERVER_PORT'];

		return $url . self::getAbsoluteUrl();
	}
	
	public static function getAbsoluteUrl()
	{
		$phpself = explode('/', $_SERVER['PHP_SELF']);
		$url = "";
		for($i=0; $i<(sizeof($phpself) - 1); $i++)
		{
		    if($i != 0 && $i != sizeof($phpself) - 2)
			    $url .= '/' . $phpself[$i];
		}
		return $url;
	}
	
	public static function getAppDir()
	{
		return ROOT_PATH. DIRECTORY_SEPARATOR;
	}
	
	/**
	 * redirect action (default file: index.php)
	 * @author phongbd
	 * @param $params - array: 0: action, 1: class, 2: area, 3: array-params
	 */
	function redirectAction($action, $controller='', $module='', $params=array())
	{
		$router = $this->router;
		$router->action = $action;
		if (is_array($controller) && !empty($controller))
		{
			$router->args = $controller;
		}
		elseif (!empty($controller))
		{
			$router->controller = $controller;
			if (is_array($module))
				$router->args = $module;
			elseif (!empty($module))
			{
				$router->module = $module;
				if (is_array($params) && !empty($params))
				{
					$router->args = $params;
				}
			}
			
		}
		$url = self::getActionUrl($router);
		header('location: '. $url);
	}
	
	/**
	 * @param $params - array: 0: action, 1: class, 2: area, 3: parrams
	 */
	function getUrlAction($params = array())
	{
		$value = '';
		$area = $params[1];
		if (is_array($params[2]))
		{
			foreach ($params[2] as $key => $p)
			{
				$value .= "&$key=$p";
			}
			$area = $params[1];
		}
		elseif (!empty($params[3]))
		{
			foreach ($params[3] as $key => $p)
			{
				$value .= "$key=$p&";
			}
			if($value != '')
				$value = substr($value, 0, -1);
			$area = $params[2];
		}
		elseif (!is_array($params[2]))
		$area = $params[2];
		if ($value != '')
			$value = '?' . $value;
		return $this->getServerPath($area . '/' . $params[1] . '/' . $params[0] . $value);
	}
	
	/**
	 * Get file path
	 */
	public static function getUrlContent($path)
	{
		$dir = ROOT_PATH . DIRECTORY_SEPARATOR . $path;
	
		return $dir;
	}
	
	function getServerPath($path)
	{
		$dir = Url::getApplicationUrl() . '/' . $path;
		return $dir;
	}
	
	function getActionUrl(Router $router)
	{
		$action = $router->action;
		$controller = $router->controller;
		$module = $router->module;
		$params = $router->args;
		if ($this->rewriteEnable)
		{
			$rewrite = new Rewrite($router);
            $url = $rewrite->getUrl();
			if (!is_null($url))
				return $url;
		}
		
		if (empty($controller)) {
			$controller = 'Index';
		}
		if (empty($action))
			$action = 'index';
		
		$valueParams = '';
		if (!empty($params))
		{
			foreach ($params as $key => $p)
			{
				if ($p != '' && !is_array($p))
					$valueParams .= "$key=$p&";
			}
			if($valueParams != '')
				$valueParams = substr($valueParams, 0, -1);
		}

        if (!empty($valueParams))
            $valueParams = '?' . $valueParams;//$valueParams = '?' . base64_encode($valueParams);
        return $this->getServerPath($module . '/' . $controller . '/' . $action . $valueParams);
	}
	
	/**
	 * Overload method action and geturl
	 */
	function __call ($method_name, $arguments)
	{
		$totalArguments = count($arguments);
		if ($method_name == 'action')
		{
			switch ($totalArguments)
			{
				case 1:
					return $this->action1($arguments[0]);
					break;
				case 2:
					if (is_array($arguments[1]))
					{
						return $this->action2($arguments[0], $arguments[1]);
					}
					else
						return $this->action6($arguments[0], $arguments[1]);
					break;
				case 3:
					if (is_array($arguments[2]))
					{
						return $this->action4($arguments[0], $arguments[1], $arguments[2]);
					}
					else
						return $this->action3($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					return $this->action5($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
                case 5:
                    return $this->action7($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
                    break;
			}
		}
	}
	
	function action1($action)
	{
		//Lấy action của router hiện tại
		$router = new Router();
		$router->module = $this->router->module;
		$router->controller = $this->router->controller;
		$router->action = $action;
		return self::getActionUrl($router);
	}
	
	function action2($action, $params)
	{
		//Lấy action của router hiện tại
		$router = new Router();
		$router->module = $this->router->module;
		$router->controller = $this->router->controller;
		$router->action = $action;
        //check params
        foreach($params as $key => $value)
        {
            if($value == '')
                unset($params[$key]);
        }
		$router->args = $params;
		return self::getActionUrl($router);
	}
	
	function action3($action, $controller, $module)
	{
		$router = new Router();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
		$router->args = array();
		return self::getActionUrl($router);
	}
	
	function action4($action, $controller, $params)
	{
		$router = new Router();
		$router->module = $this->router->module;
		$router->action = $action;
		$router->controller = $controller;
        //check params
        foreach($params as $key => $value)
        {
            if($value == '')
                unset($params[$key]);
        }
		$router->args = $params;
		return self::getActionUrl($router);
	}
	
	function action5($action, $controller, $module, $params)
	{
		$router = new Router();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
        //check params
        if(isset($params) && is_array($params))
        {
            foreach($params as $key => $value)
            {
                if($value == '')
                    unset($params[$key]);
            }
        }
		$router->args = $params;
		return self::getActionUrl($router);
	}
	
	function action6($action, $controller)
	{
		$router = new Router();
		$router->module = $this->router->module;
		$router->action = $action;
		$router->controller = $controller;
		$router->args = array();
		return self::getActionUrl($router);
	}

    function action7($action, $controller, $module, $alias, $params)
    {
        $router = new Router();
        $router->alias = $alias;
        $router->module = $this->router->module;
        $router->action = $action;
        $router->controller = $controller;
        $router->args = array();
        return self::getActionUrl($router);
    }
}
