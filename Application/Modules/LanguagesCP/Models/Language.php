<?php
#[Table('language')]
#[PrimaryKey('id')]
class Models_Language extends PTModel
{
	public $id;
	
	public $lang_code;
	
	public $name;
	
	public $isdefault;

    public static function getDefaultLangcode()
    {
        $obj = self::getInstance();
        return $obj->db->select('lang_code')->where('isdefault',1)->getcField();
    }
}