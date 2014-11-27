<?php
#[Table('gallery_image_album_category')]
#[PrimaryKey('id')]
class Models_ImgAlbumCategory extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $name;
	
	public $desc;
	
	public $order_no;

	public $parent_id;
	
	public $lang_code;

    public $status;


    public static function getCatMultiLevel($parentId=0,$langCode='vi-VN')
    {
        //Get all category
        $obj = self::getInstance();
        if($parentId)
            $obj->db->where('parent_id', $parentId);
        if(MULTI_LANGUAGE && $langCode)
            $obj->db->where('lang_code', $langCode);
        $cats = $obj->db->where('status', 1)->orderby('order_no', 'asc')->getcFieldsArray();
        $arr = array();
        foreach($cats as $key => $item)
        {
            if(isset($arr[$item['id']]['child']))
            {
                $tmp = $arr[$item['id']]['child'];
                $arr[$item['id']] = $item;
                $arr[$item['id']]['child'] = $tmp;
            }
            else
                $arr[$item['id']] = $item;
            if($item['parent_id'] > 0)
            {
                $arr[$item['parent_id']]['child'][$item['id']] = $item;
                unset($arr[$item['id']]);
            }
        }
        return $arr;
    }

    /**
     * get tree cat
     */
    public static function getTreeCat($parentId=0, $defaultValue = 'true', $langCode='vi-VN')
    {
        $listCat = array();
        if($defaultValue)
            $listCat[0] = 'Danh mục gốc';
        $arrCat = self::getCatMultiLevel($parentId, $langCode);
        $arr = self::_getTreeCat($arrCat, '');
        $listCat = $listCat + $arr;
        return $listCat;
    }

    private static function _getTreeCat($array, $prefix)
    {
        $listCat = array();
        foreach($array as $key => $item)
        {
            $listCat[$item['id']] = $prefix . $item['name'];
            if(isset($item['child']))
            {
                $tmpArr = self::_getTreeCat($item['child'], $prefix . '----');
                if($tmpArr)
                {
                    foreach($tmpArr as $k => $v)
                    {
                        $listCat[$k] = $v;
                    }
                }
            }
        }
        return $listCat;
    }

    public static function getMaxOrderNo($parentid)
    {
        $obj = self::getInstance();
        return $obj->db->select('max(order_no)')->where('parent_id', $parentid)->getcField();
    }
}
?>