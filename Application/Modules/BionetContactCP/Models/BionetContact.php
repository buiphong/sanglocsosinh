<?php
#[Table('bionet_contact_list')]
#[PrimaryKey('id')]
class Models_BionetContact extends PTModel{
	#[Update(false)]
	public $id;
	
	public $title;
	
	public $name;
	
	public $address;
	
	#[DataType('number')]
	public $order_no;
	
	public $phone;
	
	public $hotline;
	
	public $status;

    /**
     * Lấy danh sách điểm thu mẫu
     */
    public static function getList($limit = '',$status = 1)
    {
        $obj = self::getInstance();
        if(empty($status))
            $status = 0;
        $obj->db->orderby('order_no')->where('status', $status);
        if(!empty($limit))
            $obj->db->limit($limit);
        return $obj->db->getFieldsArray();
    }
}