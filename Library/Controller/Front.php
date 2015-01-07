<?php
class Controller_Front extends Controller_Abstract
{
	function __construct(&$router = null)
	{
		parent::__construct($router);
	}
	
	/**
	 * Chạy Front Controller
	 * @param $params - Danh sách tham số: cpMode, loadTemplate, skin...
	 */
	public function dispatch (Router $router = null, $method = '', $params = array())
	{
		if (empty($router))
			$router = $this->router;
        //check router
        if(empty($router->alias))
            $router->alias = 'Portal';
		//Xử lý Controller
        $router->controller = trim($router->controller);
		$class = $router->controller . 'Controller';
        $cf = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'. DIRECTORY_SEPARATOR .$router->alias. DIRECTORY_SEPARATOR .
                            $router->module . DIRECTORY_SEPARATOR . $class . '.php';
        if(!file_exists($cf))
            throw new Exception('Không tìm thấy file: ' . $class . '.php tại Application'. DIRECTORY_SEPARATOR .$router->alias. DIRECTORY_SEPARATOR .$router->module);
		require_once $cf;
		if (!class_exists($class))
        {
            throw new Exception('Không tìm thấy class: ' . $class);
        }
		$obj = new $class($router);
		//Gọi phương thức khởi tạo
		if (method_exists($obj, '__init'))
			$obj->__init();
		//Kiểm tra nếu là extend từ rest
		if ($obj instanceof RestController)
		{
			//Gán lại tham số
			$obj->params = $router->restArgs;
		}
		//Kiểm tra nếu là Presentation
		if ($obj instanceof Presentation)
		{
            //Set cache browser
            if(!DEBUG)
            {
                $seconds_to_cache = HTML_CACHETIME;
                $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
                header("Expires: $ts");
                header("Pragma: cache");
                header("Cache-Control: max-age=$seconds_to_cache");
            }

			if (isset($params['loadTemplate']) && $params['loadTemplate'] ===  false) {
				$obj->setViewMode('single');
			}
			else 
				$obj->setViewMode('full');
		}
		$incPath = get_include_path();
		$dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . $router->alias . DIRECTORY_SEPARATOR . $router->module;
		set_include_path($dirs . PATH_SEPARATOR . $incPath);
		if ($method != '')
		{
			$result = $this->_callAction($obj, "{$router->action}$method", $params);
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
				$result = $this->_callAction($obj, "{$router->action}Ajax", $params);
			}
			else
			{
				//Nếu người dùng sử dụng form POST dữ liệu thì gọi tới actionPost trước khi gọi action
				if ($_SERVER['REQUEST_METHOD'] === 'POST') {
					//Xử lý gọi actionPost
					$result = $this->_callAction($obj, "{$router->action}Post", $params);
				}
				//Nếu người dùng sử dụng form GET để gửi dữ liệu thì gọi tới actionGet trước khi gọi action
				//if (sizeof($_GET) > 1) {
				//Xử lý gọi actionPost
				//	$result = $this->_callAction($obj, "{$this->router->action}Get");
				//}
				//Nếu POST và GET không trả về View_Result => Gọi action
				if (!isset($result)) {
					//Xử lý gọi action
					$result = $this->_callAction($obj, "{$router->action}Action", $params);
				}
			}
		}
		/*
		 * XỬ LÝ KẾT QUẢ TRẢ VỀ
		*/
		return $result;
	}
	
	function _callAction($obj, $action, $params=array())
	{
		if (!method_exists($obj, $action))
        {
            return '<div>Không tìm thấy phương thức: ' . $action . '</div>';
        }
        else
        {
            if (!empty($params['skin'])) {
                $obj->setView($params['skin'], true);
            }

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
						$model = $controller->getModel();
						return $model;
					}
				}
			}
		}
		return false;
	}
}