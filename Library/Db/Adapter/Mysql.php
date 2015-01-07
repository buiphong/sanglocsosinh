<?php
class Db_Adapter_Mysql
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
		$this->connect();
	}
	
	private function connect() {
		require_once ROOT_PATH.'/Library/Db/adodb/adodb.inc.php';
        if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR))
            PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR);
		$ADODB_CACHE_DIR = ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR;
		$this->connection = ADONewConnection($this->adapter->adapter);
		$this->connection->Connect($this->adapter->server, $this->adapter->username, $this->adapter->password, $this->adapter->dbname);
		$this->connection->EXECUTE("set names 'utf8'");
		$this->connection->SetFetchMode(ADODB_FETCH_ASSOC);
		if ($this->connection->ErrorMsg()!="") die("Can not connect to database server!");
	}
}