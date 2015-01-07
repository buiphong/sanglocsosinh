<?php
class Db_Adapter_MongoDB
{
	/**
	 * @var Db_Adapter
	 */
	private $adapter;
	
	public $connection;
	public $db;
	private $connection_string;
	
	function __construct($adapter)
	{
		$this->adapter = $adapter;
		$this->connection_string();
		$this->connect();
	}
	
	private function connect() {
		$options = array();
		if($this->persist === TRUE):
		$options['persist'] = isset($this->adapter->persist_key) && !empty($this->adapter->persist_key) ? $this->persist_key : 'ci_mongo_persist';
		endif;
		try {
			$this->connection = new Mongo($this->connection_string, $options);
			$this->db = $this->connection->{$this->adapter->dbname};
			return($this);
		} catch(MongoConnectionException $e) {
			throw new Exception("Unable to connect to MongoDB: {$e->getMessage()}", 500);
		}
	}
	
	/**
	 *	--------------------------------------------------------------------------------
	 *	BUILD CONNECTION STRING
	 *	--------------------------------------------------------------------------------
	 *
	 *	Build the connection string from the config file.
	 */
	
	private function connection_string() {
		//$this->persist = trim($this->CI->config->item('mongo_persist'));
		//$this->persist_key = trim($this->CI->config->item('mongo_persist_key'));
	
		$connection_string = "mongodb://";
	
		if(empty($this->adapter->server)):
		throw new Exception("The Host must be set to connect to MongoDB", 500);
		endif;
	
		if(empty($this->adapter->dbname)):
		throw new Exception("The Database must be set to connect to MongoDB", 500);
		endif;
	
		if(!empty($this->adapter->username) && !empty($this->adapter->password)):
		$connection_string .= "{$this->adapter->username}:{$this->adapter->password}@";
		endif;
	
		if(isset($this->adapter->port) && !empty($this->adapter->port)):
		$connection_string .= "{$this->adapter->server}:{$this->adapter->port}";
		else:
		$connection_string .= "{$this->adapter->server}";
		endif;
	
		$this->connection_string = trim($connection_string);
	}
}