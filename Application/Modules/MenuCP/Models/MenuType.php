<?php
#[Table('menu_type')]
#[PrimaryKey('id')]
class Models_MenuType extends PTModel
{
	public $id;
	
	public $type_name;
	
	public $parent_id;
	
	public $type_desc;

    public $lang_code;

    public $url_title;

    public static function getName($id)
    {
        if($id)
        {
            $obj = self::getInstance();
            return $obj->db->select('type_name')->where('id', $id)->getcField();
        }
    }

    public static function getMenuTypeMultiLevel($parentId=0,$langCode='vi-VN')
    {
        //Get all menu
        $obj = self::getInstance();
        if($parentId)
            $obj->db->where('parent_id', $parentId);
        if(MULTI_LANGUAGE && $langCode)
            $obj->db->where('lang_code', $langCode);
        $cats = $obj->db->orderby('type_name')->getcFieldsArray();
        $arr = self::getChildMenuType(0, $cats);
        return $arr;
    }

    private static function getChildMenuType($parentId, $array)
    {
        $arr = array();
        foreach($array as $key => $item)
        {
            if($item['parent_id'] == $parentId)
            {
                $arr[$item['id']] = $item;
                unset($array[$key]);
                $child = self::getChildMenuType($item['id'], $array);
                if($child)
                    $arr[$item['id']]['subs'] = $child;
            }
        }
        return $arr;
    }

    public static function getIdFromUrlTitle($urlTitle)
    {
        $obj = self::getInstance();
        return $obj->db->select('id')->where('url_title', $urlTitle)->getField();
    }

    public static function getType($id = '')
    {
        $obj = self::getInstance();
        if($id)
            $obj->db->where('id', $id);
        return $obj->db->getFields();
    }

    public static function getUrlTitle($typeId)
    {
        $obj = self::getInstance();
        return $obj->db->select('url_title')->where('id', $typeId)->getField();
    }
}