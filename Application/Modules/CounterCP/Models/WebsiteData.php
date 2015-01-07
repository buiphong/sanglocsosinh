<?php
#[Table('website_data')]
#[PrimaryKey('id')]
class Models_WebsiteData extends PTModel{
	#[Update(false)]
	public $id;
	
	public $code;
	
	public $value;

    public static function getDataValue($code)
    {
        if($code)
        {
            $obj = self::getInstance();
            return $obj->db->select('value')->where('code', $code)->getField();
        }
        return false;
    }
}