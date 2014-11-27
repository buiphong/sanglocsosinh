<?php
#[Table('list_province')]
#[PrimaryKey('id')]
class Models_ListProvince extends PTModel
{
	public $id;
	
	public $name;
	
	#[DataType('number')]
	public $order_no;

    public $lang_code;

    public $status;

    public $parent_id;

    public $path;

    public static function getChilds($parentId = 0, $langCode = 'vi-VN')
    {
        //Get all menu
        $obj = self::getInstance();
        if($parentId)
            $obj->db->where("(path like '$parentId/%' or path like '%/$parentId/%')");
        if(MULTI_LANGUAGE && $langCode)
            $obj->db->where('lang_code', $langCode);
        $cats = $obj->db->orderby('name')->orderby('path')->getcFieldsArray();
        return $cats;
    }

    public static function getListMultiLevel($parentId = 0 ,$langCode='vi-VN')
    {
        $cats = self::getChilds($parentId, $langCode);
        $arr = self::_getChild($parentId, $cats);
        return $arr;
    }

    private static function _getChild($parentId, $array)
    {
        $arr = array();
        foreach($array as $key => $item)
        {
            if($item['parent_id'] == $parentId)
            {
                $arr[$item['id']]['values'] = $item;
                unset($array[$key]);
                $child = self::_getChild($item['id'], $array);
                if($child)
                    $arr[$item['id']]['subs'] = $child;
            }
        }
        return $arr;
    }

    public static function getTreeView($parentId = 0, $langCode = 'vi-VN')
    {
        $cats = self::getListMultiLevel($parentId, $langCode);
        return self::_getTreeView($cats);
    }

    private static function _getTreeView($array, $prefix = '')
    {
        $arr = array();
        if($array)
        {
            foreach($array as $k => $v)
            {
                $arr[$v['values']['id']] = $prefix . ' '.$v['values']['name'];
                if(isset($v['subs']))
                {
                    $c = self::_getTreeView($v['subs'], $prefix . '----');
                    $arr = array_merge($arr, $c);
                }
            }
        }
        return $arr;
    }

    public static function getTreeViewAttr($parentId = 0, $langCode = 'vi-VN')
    {
        $cats = self::getListMultiLevel($parentId, $langCode);
        return self::_getTreeViewAttr($cats);
    }

    private static function _getTreeViewAttr($array, $prefix = '')
    {
        $arr = array();
        if($array)
        {
            foreach($array as $k => $v)
            {
                $arr[] = array('id' => $v['values']['id'], 'title' => $prefix . ' '.$v['values']['name']);
                if(isset($v['subs']))
                {
                    $c = self::_getTreeViewAttr($v['subs'], $prefix . '----');
                    $arr = array_merge($arr, $c);
                }
            }
        }
        return $arr;
    }

    /**
     * get max order_no
     */
    public static function getMaxOrder($parentId = 0)
    {
        $obj = self::getInstance();
        return $obj->db->select('max(order_no)')->where('parent_id', $parentId)->getField();
    }
}