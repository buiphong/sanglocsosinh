<?php
/**
 * Lớp model kết nối csdl
 * @author phongbui
 */
class PTModel extends Db_Model
{
	/**
     * Thuộc tính cho phép Model tự động gắn kết với CSDL khi khởi tạo
     * @var bool
     */
    public $autoBinding = true;
    
	public function __construct($id = null)
	{
		//Lấy attribute
		$this->attributes = Attribute::getObjAttributes($this);
		//Lấy thuộc tính table, primarykey
        if(get_class($this) != 'PTModel')
        {
            $attribute = $this->attributes[get_class($this)];
            if (!empty($attribute['Table']))
                $this->_table = reset($attribute['Table']);
            if (!empty($attribute['PrimaryKey']))
                $this->_primaryKey = reset($attribute['PrimaryKey']);
            parent::__construct();
            //Tự động gắn kết model với CSDL
            if ($this->autoBinding) {
                $this->bind($id);
            }
		}
	}
	
	/**
	 * Phương thức gắn kết Model với CSDL
	 * @param mixed $id Id của Model Nếu có truyền vào ID lấy thông tin tương ứng với Id từ database
	 */
	public function bind($id = NULL)
	{
		/**
		 * Nếu có truyền vào ID lấy thông tin tương ứng với Id từ database
		 * lưu vào thuộc tính đối tượng
		 */
		if ($id !== NULL) {
			$this->get($id);
		}
	}
	
	/**
	 * Phương thức lấy giá trị từ database gán vào model hiện tại
	 * @param int $id
	 */
	public function get($id)
	{
		//Lấy dữ liệu từ database
		$row = $this->db->where($this->_primaryKey, $id)->getcFields();
		if (!isset($row))
			return false;

		//Gán giá trị vào các thuộc tính
		$properties = $this->getModelProperties();
		foreach ($properties as $p)
		{
			if(isset($row[$p]))
				$this->$p = $row[$p];
		}
		//Trả về kết quả select được
		return $row;
	}
	
	/**
	 * Lấy ra danh sách các thuộc tính của model
	 * @return array
	 */
	public function getModelProperties()
	{
		//Lấy ra đường dẫn tới tệp tin chứa class
		$reflection = new ReflectionClass($this);
		$path = $reflection->getFileName();
	
		$properties = array();
		$classPros = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
		//Lấy ra các thuộc tính của Model
		$refModel = new ReflectionClass('PTModel');
		$properties_model = array();
		foreach ($refModel->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
			$properties_model[] = $property->getName();
		}
		//lấy các thuộc tính của lớp thừa kế model
		foreach ($classPros as $property)
		{
			$property_name = $property->getName();
			if(!in_array($property_name, $properties_model))
				$properties[] = $property_name;
		}
		
		return $properties;
	}
}