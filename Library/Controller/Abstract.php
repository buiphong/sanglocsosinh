<?php
/**
 * Class Controller Abstract
* @author buiphong
*/
class Controller_Abstract
{
	/**
	 * Router của hệ thống
	 * @var Router
	 */
	public $router;

	/**
	 * Các tham số được truyền vào
	 */
	public $params;
	
	/**
	 * Model
	 */
	public $model;
	
	/**
	 * MongoDb
	 */
	public $mongodb;

	/**
	 * Khởi tạo controller
	 * @param Router $router || null
	 */
	public function __construct(Router $router = null)
	{
		//Router
		if (is_null($router))
		{
			$this->router = new Router();
			$this->router->analysisRequestUri();
            PTRegistry::set('currRouter', $this->router);
		}
		else
			$this->router = $router;
		//Lưu các tham số được truyền vào
		if (PTRegistry::isRegistered('Db_mongodb')) {
			$this->mongodb = new PTMongoDb();
		}
		$this->getParams();
	}

	public function isAjaxRequest()
	{
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return true;
		else
			return false;
	}

	function getParams()
	{
        $params = array();
		if (sizeof($_POST) > 0) {
			//Xử lý gọi actionPost
			$params = $_POST;
		}
        if(sizeof($_GET) > 0)
        {
            //Xử lý gọi action
            $params = array_merge($params, $_GET);
        }

        if (!empty($_FILES))
        {
            foreach ($_FILES as $name => $info)
            {
                $params[$name] = $info;
            }
        }
        $this->params = $params;

        foreach ($this->router->args as $key=>$value)
        {
            $this->params[$key] = $value;
        }
	}
	
	/**
	 * Thiết lập model
	 * @param PTModel $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}
	
	/**
	 * lấy ra tên model
	 */
	public function getModel()
	{
		return $this->model;
	}
	
	/**
	 * Load model
	 */
	public function loadModel($model)
	{
		$class = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $model . '.php';
		require_once $class;
		if (is_file($class)) {
			return new $class();
		}
		else 
			throw new Exception('Không tìm thấy model: ' . $model);
	}
	
	/**
	 * Gán tham số tương ứng được truyền từ post, get, ajax vào model
	 */
	public function parseModelParams()
	{
		$hasProperty = false;
		if (is_object($this->model))
		{
			//Lấy thuộc tính của model
			$ref = new ReflectionClass($this->model);
			$properties = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach ($properties as $property)
			{
				$propertyNames[] = $property->getName();	
			}
			//Gán giá trị có từ params vào model
			foreach ($propertyNames as $property)
			{
				if (isset($this->params[$property]))
				{
					$this->model->$property = String::secure($this->params[$property]);
					$hasProperty = true;
				}
				else 
					continue;
			}
		}
		return $hasProperty;
	}

    public function loadModule($modules)
    {
        $dirs = '';
        if(is_array($modules))
        {
            foreach($modules as $m)
            {
                if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $m))
                    throw new Exception('Không tìm thấy module: ' . $m);
                if(empty($dirs))
                    $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $m;
                else
                    $dirs .= PATH_SEPARATOR . ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $m;
            }
        }
        elseif(!empty($modules))
        {
            if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $modules))
                throw new Exception('Không tìm thấy module: ' . $modules);
            $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $modules;
        }
        $incPath = get_include_path();
        set_include_path($incPath . PATH_SEPARATOR . $dirs);
    }
}