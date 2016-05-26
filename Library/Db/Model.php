<?php
require_once ROOT_PATH.'/Library/Db/adodb/adodb.inc.php';
class Db_Model
{
	/**
	 * 
	 * @var ADOConnection
	 */
	var $conn;
	var $caching = false;
	var $cacheType = "file";
	var $cacheTime = CACHETIME;
	var $sql = "";
	var $error = "";
	
	/**
	 * Tên bản hiện tại
	 * @var string
	 */
	public $_table;
	
	/**
	 * Khóa chính
	 * @var string
	 */
	public $_primaryKey;
	
	/**
	 * Thuộc tính
	 * @var array
	 */
	public $attributes;
	
	/**
	 * Lớp hỗ trợ truy vấn csdl
	 * @var Db_PTCommon
	 */
	public $db;

    /**
     * @var Db_Model
     */
    private static $_instance;
	
	public function __construct($conn = false)
	{
		if ($conn)
			$this->conn = $conn;
		elseif (PTRegistry::isRegistered('Db_mysqli'))
		{
			$adapter = PTRegistry::get('Db_mysqli');
			$this->conn = $adapter->connection;
		}
		else 
		{
            $adapter = new Db_Adapter();
            global $dbConfig;
            foreach ($dbConfig as $name => $config)
            {
                $adapter->adapter = $name;
                $adapter->loadConfig($config);
            }
            $adapter = PTRegistry::get('Db_mysqli');
            $this->conn = $adapter->connection;
		}

		$this->db = new Db_PTCommon($this->conn);
		$this->db->setTable($this->_table);
	}
	
	/**
	 * Thêm mới dữ liệu
	 */
	function Insert($arrData = array())
	{
		if(empty($arrData))
		{
			$listFields = $this->getModelProperties();
			foreach ($listFields as $field)
			{
				if (!is_null($this->$field) && $this->$field != '' && $this->$field != 'null')
				{
					$insert = true;
					//Kiểm tra attribute
					$pAttribute = Attribute::getAttributeProperty($this, $field);
					//check dataType, insert permission
					if (!empty($pAttribute))
					{
						if (isset($pAttribute['Insert']) && reset($pAttribute['Insert']) === false)
							$insert = false;
						if (isset($pAttribute['DataType']) && reset($pAttribute['DataType']) === 'number')
						{
							if (empty($this->$field))
								$this->$field = 0;
						}
					}
					if ($insert)
						$arrData[$field] = $this->$field;
				}
			}
		}
		if (empty($arrData[$this->_primaryKey]))
		{
			unset($arrData[$this->_primaryKey]);
		}
		$result = $this->db->Insert($this->_table, $arrData);
		if($result == false)
		{
			$this->error = $this->db->error;
			return false;
		}
		else
			return true;
	}
	
	/**
	 * Update dữ liệu
	 */
	public function Update($arrData = array())
	{
		if (empty($arrData) || !is_array($arrData))
		{
			//Lấy dữ liệu từ model
			$listFields = $this->getModelProperties();
			foreach ($listFields as $field)
			{
				if (!is_null($this->$field))
				{
					$update = true;
					//Kiểm tra attribute
					$pAttribute = Attribute::getAttributeProperty($this, $field);
					//check dataType, insert permission
					if (!empty($pAttribute))
					{
						if (isset($pAttribute['Update']) && reset($pAttribute['Update']) === false)
							$update = false;
						if (isset($pAttribute['DataType']) && reset($pAttribute['DataType']) === 'number')
						{
							if (empty($this->$field))
								$this->$field = 0;
						}
					}
					if ($update)
						$data[$field] = $this->$field;
				}
			}
		}
		else
			$data = $arrData;
		//Điều kiện update
		if (empty($this->db->ar_where) && empty($this->db->ar_like))
			$this->db->where($this->_primaryKey, $this->{$this->_primaryKey});
		if (!empty($data))
		{
			if($this->db->Update($data))
				return true;
			else
			{
				$this->error = $this->db->error;
				return false;
			}
		}
		else
		{
			//Thực hiện update model với dữ liệu có sẵn
			$this->error = 'Dữ liệu đưa vào update không đúng';
			return false;
		}
	}
	
	/**
	 * Xóa dữ liệu
	 */
	public function Delete($condition)
	{
		if (!empty($condition))
		{
			if ($this->db->Delete($this->_table, $condition))
				return true;
			else
			{
				$this->error = $this->db->error;
				return false;
			}
		}
		else
		{
			//Thực hiện update model với dữ liệu có sẵn
			$this->error = 'Thiếu điều kiện xóa';
			return false;
		}
	}
	
	/**
	 * Đếm số lượng bản ghi
	 */
	public function Count($condition = '')
	{
		$this->sql = "select count(`$this->_primaryKey`) from $this->_table";
		if(!empty($condition))
			$this->sql .= " where $condition";
		$result = $this->conn->GetOne($this->sql);
		if ($result === false)
		{
			$this->error = $this->conn->ErrorMsg() . '. SQL: "'.$this->sql.'"';
			return false;
		}
		else
			return $result;
	}

    public static function getInstance()
    {
        $c = get_called_class();
        if (!isset(self::$_instance[$c])) {
            self::$_instance[$c] = new $c();
        }
        return self::$_instance[$c];
    }

    public static function getById($id)
    {
        $db = self::getInstance();
        return $db->db->where($db->_primaryKey, $id)->getcFields();
    }

    /**
     * run sql
     */
    public static function runSQL($sql)
    {
        $obj = self::getInstance();
        $result = $obj->db->Execute($sql);
        if($result)
            return true;
        else
            return $obj->db->error;
    }

    public static function getFieldsArray($sql)
    {
        $obj = self::getInstance();
        return $obj->db->getFieldsArray($sql);
    }

    public static function getcFieldsArray($sql)
    {
        $obj = self::getInstance();
        return $obj->db->getcFieldsArray($sql);
    }

    public static function getFields($sql)
    {
        $obj = self::getInstance();
        return $obj->db->getFields($sql);
    }

    public static function getcFields($sql)
    {
        $obj = self::getInstance();
        return $obj->db->getcFields($sql);
    }

    public static function getcField($sql)
    {
        $obj = self::getInstance();
        return $obj->db->getcField($sql);
    }

    public static function getFieldArray($sql)
    {
        $obj = self::getInstance();
        return $obj->db->error;
    }
}