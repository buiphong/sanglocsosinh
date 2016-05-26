<?php
class Db_PTCommon
{
	/**
	 * ADODB
	 * @var ADOConnection
	 */
	var $conn;
	var $caching = false;
	var $cacheType = "file";
	var $cacheTime = 30;
	var $sql = "";
	var $error = "";
	
	public $ar_select;

    public $ar_distinct;
	
	public $ar_where;
	
	public $ar_wherein;
	
	public $ar_orderby;
	
	public $ar_groupby;
	
	public $ar_join;
	
	public $ar_from;
	
	public $ar_like;
	
	public $limit;
	
	public $offset;
	
	private $_table;
	
	private $_primaryKey;
	
	function Db_PTCommon($c=false){
		$this->conn = $c;
        if(PTRegistry::isRegistered('CacheTime'))
            $this->cacheTime = PTRegistry::get('CacheTime');
	}
	
	/**
	 * Gán tên table, khóa chính
	 */
	function setTable($table)
	{
		$this->_table = $table;
	}
	
	/**
	 * Thiết lập thời gian cache
	 * @param $time milisecond
	 */
	public function setCacheTime($time)
	{
		$this->cacheTime = $time;
	}
	
	public function Insert($table, $fieldList){
		$fldList = $this->quoteTable($table, $fieldList);
		$isql = "INSERT INTO ".$table." (`".implode("`, `", array_keys($fldList))."`) VALUES (".implode(", ", array_values($fldList)).")";
		$this->sql = $isql;
		if ($this->conn->Execute($isql))
			return true;
		else
		{
			$this->error = $this->conn->ErrorMsg() . ". SQL: $this->sql";
			return false;
		}
	}
	
	function Delete($table='', $cond='')
	{
		if (empty($table))
			$table = $this->_table;
		if (empty($cond))
			$cond = implode(' ', $this->ar_where);
		$sql = "delete from $table where $cond";
		$this->sql = $sql;
		$this->_reset_select();
		if($this->conn->Execute($sql))
			return true;
		else
		{
			$this->error = $this->conn->ErrorMsg() . ". SQL: $this->sql";
			return false;
		}
	}
	function InsertId()
	{
		return $this->conn->Insert_ID();
	}
	public function count($resetSelect = false)
	{
        if($this->ar_distinct)
        {
            $select = "Select count(DISTINCT ";
            if (count($this->ar_select) == 0 && $this->ar_distinct === true)
                $select .= $this->_table . '.*';
            elseif($this->ar_distinct != '')
                $select .= $this->ar_distinct;
            elseif(count($this->ar_select) > 0)
                $select .= implode(',', $this->ar_select);
            $select .= ') as total';
        }
        else
		    $select = "Select count(*) as total";
        if(count($this->ar_groupby) > 0)
        {
            $sql = $this->_compile_select($select);
            $result = $this->conn->GetAll($sql);
            if($result)
                $result = count($result);
            else
            {
                $this->error = $this->conn->ErrorMsg();
                return false;
            }
        }
        else
        {
            $sql = $this->_compile_select($select);
            $result = $this->conn->GetOne($sql);
        }
		if ($resetSelect === true) {
			$this->_reset_select();
		}
		if ($result == false) {
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	public function select($select = '*')
	{
		if (is_string($select))
			$select = explode(',', $select);
		foreach ($select as $field)
		{
			if (!empty($field))
				$this->ar_select[] = $this->_protect_identifiers($field);
		}
		return $this;
	}

    public function distinct($val = TRUE)
    {
        $this->ar_distinct = (is_bool($val)) ? TRUE : $val;
        return $this;
    }
	
	public function from($table)
	{
		$this->ar_from[] = $this->_escape_identifiers($table);
		return $this;
	}
	
	public function join($table, $cond, $type = '')
	{
		if ($type != '')
		{
			$type = strtoupper($type);
			if (!in_array($type, array('LEFT','RIGHT','INNER','OUTER','LEFT OUTER', 'RIGHT OUTER')))
				$type = '';
			else
				$type .= ' ';
		}
		$join = $type.'JOIN ' . $this->_escape_identifiers($table) . ' ON ' . $cond;
		$this->ar_join[] = $join;
		return $this;
	}
	
	public function where($key, $value=null, $escape = false)
	{
		$this->_where($key, $value, 'AND ', $escape);
		return $this;
	}
	
	public function or_where($key, $value=null, $escape = false)
	{
		$this->_where($key, $value, 'OR ', $escape);
		return $this;	
	}
	
	public function where_in($key, $values=null, $escape=false)
	{
		$this->_where_in($key, $values);
		return $this;
	}
	
	public function or_where_in($key, $values=null, $escape)
	{
		$this->_where_in($key, $values, false, 'OR ');
		return $this;
	}
	
	public function where_not_in($key, $values=null, $escape=false)
	{
		$this->_where_in($key, $values, true);
		return $this;
	}
	
	public function or_where_not_in($key, $values=null, $escape=false)
	{
		$this->_where_in($key, $values, true, 'OR ');
		return $this;
	}
	
	public function _where_in($key = null, $values=null, $not=false, $type = 'AND ')
	{
		if ($key === NULL or $values === NULL) {
			return;
		}
		if (! is_array($values)) {
			$values = array($values);
		}
		$not = ($not) ? ' NOT' : '';
		foreach ($values as $value) {
			$this->ar_wherein[] = $value;
		}
		$prefix = (count($this->ar_where) == 0) ? '' : $type;
		$where_in = $prefix . $this->_protect_identifiers($key) . $not . " IN (" .
				implode(", ", $this->ar_wherein) . ") ";
		$this->ar_where[] = $where_in;
		// reset the array for multiple calls
		$this->ar_wherein = array();
		return $this;
	}
	
	public function orderby($field, $type='')
	{
		if (trim($type) != '')
		{
			$type = (in_array(strtoupper(trim($type)),array('ASC', 'DESC'), TRUE)) ? ' ' . $type : ' ASC';
		}
		//else 
		//	$type = ' ASC';
		
		$this->ar_orderby[] = $this->_escape_identifiers($field) . ' ' . $type;
		return $this;
	}
	
	public function group_by($by)
	{
		if (is_string($by)) {
			$by = explode(',', $by);
		}
		foreach ($by as $val) {
			$val = trim($val);
			if ($val != '') {
				$this->ar_groupby[] = $this->_protect_identifiers($val);
			}
		}
		return $this;
	}
	
	public function limit($pageSize, $offset=0)
	{
		$this->limit = (int)$pageSize;
		$this->offset = (int)$offset;
		return $this;
	}
	
 	/**
     * Like
     *
     * Tạo ra %LIKE% của một câu truy vấn.
     * Gọi phương thức nhiều lần để AND
     *
     * @access	public
     * @param	mixed
     * @param	mixed
     * @return	Db_PTCommon
     */
    public function like ($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'AND ', $side);
    }
    // --------------------------------------------------------------------
    /**
     * Not Like
     *
     * Tạo ra NOT LIKE của một câu truy vấn.
     * Gọi phương thức nhiều lần để AND
     *
     * @access	public
     * @param	mixed
     * @param	mixed
     * @return	Db_PTCommon
     */
    public function not_like ($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'AND ', $side, 'NOT');
    }
    // --------------------------------------------------------------------
    /**
     * OR Like
     *
     * Tạo ra %LIKE% trong câu truy vấn.
     * Gọi phương thức nhiều lần để OR
     *
     * @access	public
     * @param	mixed
     * @param	mixed
     * @return	Db_PTCommon
     */
    public function or_like ($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side);
    }
    // --------------------------------------------------------------------
    /**
     * OR Not Like
     *
     * Tạo ra một NOT LIKE trong một câu truy vấn
     * Gọi phương thức nhiều lần để OR
     *
     * @access	public
     * @param	mixed
     * @param	mixed
     * @return	Db_PTCommon
     */
    public function or_not_like ($field, $match = '', $side = 'both')
    {
        return $this->_like($field, $match, 'OR ', $side, 'NOT');
    }
    // --------------------------------------------------------------------
    /**
     * orlike() là một tên khác của or_like()
     * @return Db_PTCommon
     */
    public function orlike ($field, $match = '', $side = 'both')
    {
        return $this->or_like($field, $match, $side);
    }
    // --------------------------------------------------------------------
    /**
     * Like
     *
     * Được gọi bởi like() hoặc orlike()
     *
     * @access	private
     * @param	mixed
     * @param	mixed
     * @param	string
     * @return	Db_PTCommon
     */
    public function _like ($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
    {
        if (! is_array($field)) {
            $field = array($field => $match);
        }
        foreach ($field as $k => $v) {
        	$k = $this->_escape_identifiers($k);
            $prefix = (count($this->ar_like) == 0) ? '' : $type;
            if ($side == 'before') {
                $like_statement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $like_statement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $like_statement = $prefix . " $k $not LIKE '%{$v}%'";
            }

            $this->ar_like[] = $like_statement;
        }
        return $this;
    }
	
	public function _where($key, $value=null, $type = 'AND ', $escape = false)
	{
		if (!empty($key))
		{
			if (!is_null($value))
			{
				if (!is_array($key))
					$key = array($key => $value);
				foreach ($key as $k => $v)
				{
					$prefix = count($this->ar_where) == 0 ? '' : $type;
					if (is_null($v) && ! $this->_has_operator($k)) {
						// value appears not to have been set, assign the test to IS NULL
						$k .= ' IS NULL';
					}
					if (! is_null($v)) {
						if (! $this->_has_operator($k)) {
							$k .= ' =';
						}
						$v = "'" . $v . "'";
					}
					$k = $this->_protect_identifiers($k);
					$this->ar_where[] = $prefix . $k . $v;
				}
			}
			else
			{
				$prefix = count($this->ar_where) == 0 ? '' : $type;
				$this->ar_where[] = $prefix . $key;
			}
		}
		return $this;
	}
	
	/**
	 * Tạo câu truy vấn
	 */
	public function _compile_select($select_override = false)
	{
		if ($select_override != false) {
			$sql = $select_override;
		}
		else 
		{
			$sql = (!$this->ar_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';
			if (count($this->ar_select) == 0)
				$sql .= '*';
			else
			{
				$sql .= implode(',', $this->ar_select);
			}
		}
		if (count($this->ar_from) > 0)
		{
			$sql .= ' From ' . implode(',', $this->ar_from);
		}
		else
			$sql .= ' From ' . $this->_table;
		
		if (count($this->ar_join) > 0)
		{
			$sql .= ' '.implode(' ', $this->ar_join);
		}
		
		if (count($this->ar_where) >0 || count($this->ar_like))
		{
			$sql .= ' Where ';
		}
		if(!empty($this->ar_where))
		    $sql .= implode(' ', $this->ar_where);
		
		if (count($this->ar_like) > 0)
		{
			if (count($this->ar_where) > 0)
				$sql .= ' And';
			$sql .= implode(' ', $this->ar_like);
		}
		
		if (count($this->ar_groupby) > 0) {
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $this->ar_groupby);
		}
		
		if (count($this->ar_orderby) > 0)
		{
			$sql .= ' Order By ' . implode(',', $this->ar_orderby);
		}
		
		if (!empty($this->limit))
		{
			if (empty($this->offset))
				$this->offset = 0;
			$sql .= ' limit ' . $this->offset . ',' . $this->limit;
		}
		return $sql;
	}
	
	/**
	 * Thực hiện update dữ liệu
	 */
	public function update($fields_values = array())
	{
		return $this->_update($fields_values);
	}
	
	public function _update($fields_values = array())
	{
		$sql = 'Update ' . $this->_table . ' Set ';
		foreach ($fields_values as $field => $value)
		{
			$sql .= $this->_escape_identifiers($field) . "='". str_replace("'", "\'", $value) ."',";
		}
		$sql = substr($sql, 0, -1);
		if (!empty($this->ar_where))
        {
            $sql .= ' Where ';
            $sql .=  implode(' ', $this->ar_where);
        }
		if (!empty($this->ar_like))
		{
			if (!empty($this->ar_where))
				$sql .= ' AND';
			$sql .= implode(' ', $this->ar_like);
		}
		$result = $this->conn->Execute($sql);
		$this->_reset_select();
		if ($result == false)
		{
			$this->error = $this->conn->ErrorMsg() . '. SQL: ' . $sql;
			return false;
		}
		return true;
	}
	
	/**
	 * Biên dịch và chạy câu truy vấn - lấy tất cả
	 */
	public function getAll($sql = '')
	{
		if (empty($sql))
			$sql = $this->_compile_select();
		$result = $this->conn->GetAll($sql);
		$this->_reset_select();
		if ($result === false) {
			throw new Exception($this->conn->ErrorMsg().  '. SQL: ' . $sql);
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * Lấy ra một bản ghi
	 */
	public function getRow()
	{
		$sql = $this->_compile_select();
		$result = $this->conn->GetRow($sql);
		$this->_reset_select();
		if ($result == false) {
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * Lấy ra một trường của 1 bản ghi
	 */
	public function getOne($cache = false)
	{
		$sql = $this->_compile_select();
		if ($cache === true)
			$result = $this->conn->CacheGetOne($this->cacheTime, $sql);
		else
			$result = $this->conn->GetOne($sql);
		$this->_reset_select();
		if ($result) {
			return $result;
		}
		else
		{
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
	}
	
	/**
	 * Lấy một trường của 1 bản ghi
	 * Tương tự với getOne
	 */
	public function getField($sql = '')
	{
		return $this->_getField($sql);
	}
	
	/**
	 * Lấy một trường của 1 bản ghi, có sử dụng cache
	 */
	public function getcField($sql = '')
	{
		return $this->_getField($sql,true);
	}
	
	function  _getField($sql = '', $cache = false)
	{
        if(empty($sql))
		    $sql = $this->_compile_select();
		if ($cache === true)
			$result = $this->conn->CacheGetOne($this->cacheTime, $sql);
		else
			$result = $this->conn->GetOne($sql);
		$this->_reset_select();
		if ($result) {
			return $result;
		}
		else
		{
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
	}
	
	/**
	 * Lấy ra nhiều trường của một bản ghi
	 * Tương tự với getRow
	 */
	public function getFields($sql = '')
	{
		return $this->_getFields($sql);
	}
	
	/**
	 * Lấy ra nhiều trường của một bản ghi, có sử dụng cache
	 */
	public function getcFields($sql = '')
	{
		return $this->_getFields($sql, true);
	}
	
	function _getFields($sql = '',$cache = false)
	{
        if(empty($sql))
		    $sql = $this->_compile_select();
		if ($cache === false)
			$result = $this->conn->GetRow($sql);
		else
			$result = $this->conn->CacheGetRow($this->cacheTime, $sql);
		
		$this->_reset_select();
		if ($result == false) {
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * Lấy ra một trường của nhiều bản ghi
	 */
	public function getFieldArray($sql = '', $cache = false)
	{
        if(empty($sql))
		    $sql = $this->_compile_select();
		if ($cache === false)
            $tmprs = $this->conn->GetAll($sql);
		else
            $tmprs = $this->conn->CacheGetAll($this->cacheTime, $sql);
		$this->_reset_select();
		if (!$tmprs) {
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
		else
		{
            $ret = array();
            foreach ($tmprs as $row)
            {
            	$ret[] = reset($row);
            }
            return $ret;
		}
	}
	
	public function getcFieldArray($sql = '')
	{
		return $this->getFieldArray($sql, true);
	}
	
	/**
	 * Lấy ra nhiều trường, nhiều bản ghi
	 */
	public function getFieldsArray($sql = '')
	{
		return $this->_getFieldsArray($sql);	
	}
	
	/**
	 * Lấy nhiều trường, nhiều bản ghi, có sử dụng cache
	 */
	public function getcFieldsArray($sql = '')
	{
		return $this->_getFieldsArray($sql, true);
	}
	
	function _getFieldsArray($sql, $cache = false)
	{
		if (empty($sql))
			$sql = $this->_compile_select();
        //echo '<br/>'.$sql;
		if ($cache === false)
			$result = $this->conn->GetAll($sql);
		else 
			$result = $this->conn->CacheGetAll($this->cacheTime, $sql);
		$this->_reset_select();
		if ($result === false) {
			throw new Exception($this->conn->ErrorMsg().  '. SQL: ' . $sql);
			$this->error = $this->conn->ErrorMsg();
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Đưa mọi tham số về giá trị mặc định
	 */
	public function _reset_select()
	{
		$arrReset = array('ar_order' => array(), 'ar_select' => array(), 'ar_where' => array(), 'ar_groupby' => array(),
				'ar_join' => array(), 'ar_from' => array(), 'ar_orderby' => array(), 'ar_like' => array());
		foreach ($arrReset as $key => $value)
			$this->$key = $value;
        $this->limit = '';
	}
	
	/**
	 * Kiểm tra xem chuỗi có một toán tử SQL hay không ?
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 */
	public function _has_operator ($str)
	{
		$str = trim($str);
		if (! preg_match('/(\s|<|>|!|=|is null|is not null)/i', $str)) {
			return FALSE;
		}
		return TRUE;
	}
	
	public function Execute($sql)
	{
		return $this->conn->Execute($sql);
	}
	
	function quoteTable($table, $orow){
		if (!$orow) return false;
		if ($table=="") return $orow;
		$row = $orow;
		$colArr = $this->conn->MetaColumns($table);
		foreach($colArr as $col=>$colObj){
			$colname = $colObj->name;
			$coltype = "@".$colObj->type;
			if (in_array($colname, array_keys($orow))){
				if (strpos($coltype, "char")>0 || strpos($coltype, "text")>0 || strpos($coltype, "time")>0 || strpos($coltype, "date")>0){
                    if (substr($row[$colname],0,1)!="'") {
                        if (strpos($coltype, "time")>0 || strpos($coltype, "date")>0){
                            if ($row[$colname]!="" && strtolower($row[$colname])!="null") {
                                $row[$colname] = "'".$row[$colname]."'";
                            }
                        } else $row[$colname] = "'".addslashes($row[$colname])."'";
                    }//end if has no quote yet
				}
			}
		}//end for
		return $row;
	}
	
	private $_reserved_identifiers = array('*'); // Identifiers that should NOT be escaped
	// --------------------------------------------------------------------
	/**
	 * Escape the SQL Identifiers
	 *
	 * This function escapes column and table names
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	public function _escape_identifiers ($item)
	{
		foreach ($this->_reserved_identifiers as $id) {
			if (strpos($item, '.' . $id) !== FALSE) {
				$str = '`' . str_replace('.', '`.', $item);
				// remove duplicates if the user already included the escape
				return preg_replace('/[`]+/','`', $str);
			}
		}
		if (strpos($item, '.') !== FALSE) {
			$str = '`' . str_replace('.','`.`', $item) . '`';
		} else {
			$str = '`' . $item . '`';
		}
		// remove duplicates if the user already included the escape
		return preg_replace('/[`]+/', '`', $str);
	}
	
	public function _protect_identifiers($item)
	{
		if (is_array($item)) {
			$escaped_array = array();
			foreach ($item as $k => $v) {
				$escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v);
			}
			return $escaped_array;
		}
		// Convert tabs or multiple spaces into single spaces
		$item = preg_replace('/[\t ]+/', ' ', $item);
		$item = trim($item);
		// If the item has an alias declaration we remove it and set it aside.
		// Basically we remove everything to the right of the first space
		$alias = '';
		if (strpos($item, ' ') !== FALSE) {
			$alias = strstr($item, " ");
			$tpl = substr($item, 0, - strlen($alias));
			if (!empty($tpl) && !empty($alias))
				$item = $tpl;
			else
				$item = trim($item);
			
		}
		// This is basically a bug fix for queries that use MAX, MIN, etc.
		// If a parenthesis is found we know that we do not need to
		// escape the data or add a prefix.  There's probably a more graceful
		// way to deal with this, but I'm not thinking of it -- Rick
		if (strpos($item, '(') !== FALSE) {
			return $item . $alias;
		}
		// Break the string apart if it contains periods, then insert the table prefix
		// in the correct location, assuming the period doesn't indicate that we're dealing
		// with an alias. While we're at it, we will escape the components
		if (strpos($item, '.') !== FALSE) {
			$parts = explode('.', $item);
			if (!in_array($parts[1], $this->_reserved_identifiers))
				$parts[1] = $this->_escape_identifiers($parts[1]);
			$item = $this->_escape_identifiers($parts[0]) . '.' . $parts[1];
			return $item . $alias;
		}
		return $this->_escape_identifiers($item) . $alias;
	}
}