<?php
class Db_Adapter
{
	/**
	 * Server
	 * @var string
	 */
	public $server;
	/**
	 * Port
	 * @var int
	 */
	public $port;
	/**
	 * Tên CSDL
	 * @var string
	 */
	public $dbname;
	/**
	 * Tên user truy cập CSDL
	 * @var string
	 */
	public $username;
	/**
	 * Mật khẩu user truy cập CSDL
	 * @var string
	 */
	public $password;
	
	/**
	 * Tên adapter hỗ trợ truy xuất CSDL
	 * @var string
	 */
	public $adapter;
	
	/**
	 * MongoDB persistKey
	 * @var string
	 */
	public $persistKey;
	
	/**
	 * MongoDB persist
	 * @var string
	 */
	public $persist;
	
	function __construct($config = array())
	{
		if(isset($config['name']))
			$this->name = $config['name'];
		if(isset($config['default']))
			$this->default = $config['default'];
		if(isset($config['server']))
			$this->server = $config['server'];
		if(isset($config['dbname']))
			$this->dbname = $config['dbname'];
		if(isset($config['username']))
			$this->username = $config['username'];
		if(isset($config['password']))
			$this->password = $config['password'];
		if(isset($config['persist']))
			$this->persist = $config['persist'];
		if(isset($config['persistKey']))
			$this->persistKey = $config['persistKey'];
		if(isset($config['adapter']) && !empty($config['adapter']))
			$this->adapter = $config['adapter'];
		else
			$this->adapter = 'mysqli';
	}
	
	public function loadConfig($configs = array())
	{
		foreach ($configs as $key => $value)
		{
			$this->$key = $value;
		}
		$dbAdapter = $this->callAdapter();
		//Load to registry
        PTRegistry::set('Db_' . $this->adapter, $dbAdapter);
	}
	
	private function callAdapter()
	{
		$class = '';
		switch ($this->adapter)
		{
			case 'mysql':
				$class = 'Db_Adapter_Mysql';
				break;
			case 'mongodb':
				$class = 'Db_Adapter_MongoDB';
				break;
			default:
				$class = 'Db_Adapter_Mysql';
				break;
		}
		
		return new $class($this);
	}
}