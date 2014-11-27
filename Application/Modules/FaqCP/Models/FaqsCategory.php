<?php
#[Table('faq_category')]
#[PrimaryKey('id')]
class Models_FaqsCategory extends PTModel
{
	#[Update('False')]
	public $id;
	public $name;
	public $lang_code;
	public $orderno;

    /**
     * Lấy danh sách danh mục faq
     */
    public function getListCatMultiLevel($parentId = 0, $langId = 'vi-VN')
    {
        return $this->_getListCatMultiLevel($parentId, $langId);
    }

    private function _getListCatMultiLevel($parentId = 0, $langId = 'vi-VN')
    {
        $cats = $this->db->select('id,name,parent_id')->where('parent_id', $parentId)->where('lang_code', $langId)->getAll();
        if (!empty($cats))
        {
            foreach ($cats as $key => $cat)
            {
                $childs = $this->_getListCatMultiLevel($cat['id'], $langId);
                if ($childs)
                    $cats[$key]['subs'] = $childs;
            }
            return $cats;
        }
        else
            return false;
    }

    /**
     * get name
     */
    public static function getName($id)
    {
        if($id)
        {
            $obj = self::getInstance();
            return $obj->db->where('id', $id)->select('name')->getcField();
        }
        return false;
    }
}