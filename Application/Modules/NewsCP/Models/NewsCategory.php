<?php
#[Table('news_category')]
#[PrimaryKey('id')]
class Models_NewsCategory extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $title;
	
	public $image_path;
	
	public $url_title;
	
	public $parent_id;
	
	#[DataType('number')]
	public $orderno;
	
	public $keywords;
	
	public $description;
	
	public $path;
	
	public $has_rss;
	
	public $lang_code;
	
	public $is_member;

    public $has_special;
	
	/**
	 * Lấy danh sách danh mục dưới dạng cây
	 */
	function getTreeCategory($parentId=0, $default = true, $langId='')
	{
		$this->_listCat = array();
		if ($default)
			$this->_listCat['0'] = 'Danh mục gốc';
		return $this->_getTreeCategory($parentId,'', $langId);
	}
	
	private $_listCat = array();
	
	private function _getTreeCategory($parentId = 0, $prefix = '', $langId = '')
	{
		//Lấy danh sách danh mục
        if($langId)
            $this->db->where('lang_code', $langId);
		$cats = $this->db->select('id,title')->where('parent_id', $parentId)
						->orderby('orderno')->getAll();
		if( !empty($cats))
		{
			foreach ($cats as $key => $cat)
			{
				$this->_listCat[$cat['id']] = $prefix . $cat['title'];
				$this->_getTreeCategory($cat['id'], $prefix . '----', $langId);
			}
		}
		return $this->_listCat;
	}

    public static function getCatMultiLevel($parentId=0, $langCode='')
    {
        //Get all category
        $obj = self::getInstance();
        if($parentId)
            $obj->db->where('parent_id', $parentId);
        if(MULTI_LANGUAGE && $langCode)
            $obj->db->where('lang_code', $langCode);
        $cats = $obj->db->orderby('orderno')->getcFieldsArray();
        $arr = array();
        foreach($cats as $key => $item)
        {
            if(isset($arr[$item['id']]['subs']))
            {
                $tmp = $arr[$item['id']]['subs'];
                $arr[$item['id']] = $item;
                $arr[$item['id']]['subs'] = $tmp;
            }
            else
                $arr[$item['id']] = $item;
            if($item['parent_id'] > 0)
            {
                $arr[$item['parent_id']]['subs'][$item['id']] = $item;
                unset($arr[$item['id']]);
            }
        }
        return $arr;
    }
	
	/**
	 * Lấy danh sách danh mục tin
	 */
	public function getListCatMultiLevel($parentId = 0, $langId = '')
	{
		return $this->_getListCatMultiLevel($parentId, $langId);
	}
	
	private function _getListCatMultiLevel($parentId = 0, $langId = '')
	{
        if($langId)
            $this->db->where('lang_code', $langId);
		$cats = $this->db->select('id,title,parent_id')->where('parent_id', $parentId)->getAll();
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

    private $_childCat;
    public function getChildCat($catId,$langCode = 'vi-VN')
    {
        $this->_childCat = array();
        $this->_getChildCat($catId);
        return $this->_childCat;
    }
    private function _getChildCat($catid,$langCode = 'vi-VN')
    {
        $childs = $this->db->select('id,title,url_title')->where('parent_id', $catid)->where('lang_code', $langCode)->getFieldsArray();
        if(!empty($childs))
        {
            foreach($childs as $child)
            {
                $this->_childCat[] = $child;
                $this->_getChildCat($child['id']);
            }
        }
    }
	
	private $_arrParent;
	
	/**
	 * get array parent
	 */
	public function getArrayParent($catId)
	{
		$this->_arrParent = array();
		$this->_getArrayParent($catId);
		return $this->_arrParent;
	}
	
	private function _getArrayParent($catId)
	{
		if (!empty($catId)) {
			$cat = $this->db->select('id,title,parent_id')->where('id', $catId)->getFields();
			array_unshift($this->_arrParent, $cat);		
			if (!empty($cat['parent_id'])) {					
				return $this->_getArrayParent($cat['parent_id']);
			}
		}
	}
	/*Lấy các danh mục cha  */
	private $arrCat = array();
	//private $catRoot;
	public function getCatParent($catId,$root=1, $prev = 0)
	{
        $path = $this->db->select('path')->where('id', $catId)->getField();
        if(!empty($path))
        {
            $arr = explode('/', $path);
            $this->db->select('id,title');
            foreach($arr as $i)
            {
                $this->db->or_where('id', $i);
            }
            $cats = $this->db->getFieldsArray();
            foreach($cats as $k => $v)
            {
                $cats[$k]['urlTitle'] = String::seo($v['title']);
            }
            return $cats;
        }
        else
        {
            $this->arrCat = array();
            return $this->_getCatParent($catId, $prev, $root);
        }
	}
	public function _getCatParent($catId, $prev = 0, $root)
	{
        $model = new Models_NewsCategory($catId);
        $this->arrCat[$catId] = array('urlTitle' => String::seo($model->title), 'title' => $model->title, 'id' => $catId);
		if($model->parent_id != 0 && $model->parent_id != $prev)
		{
			return $this->_getCatParent($model->parent_id,$catId, $root);
		}
		else
		{
			$arrCat = array_reverse($this->arrCat);
			unset($this->arrCat);
			return $arrCat;
		}
	}
	/*Danh mục dành cho thành viên */
	private $catMember;
	public function getCatMember()
	{
		if(empty($_SESSION['member']))
		{
			$modelNewCat = new Models_NewsCategory();
			$cats = $modelNewCat->db->select('id')->where('is_member',1)->getcFieldArray();
			if(!empty($cats))
			{
				$this->catMember = implode(',', $cats);
				return $this->_getCatMember($this->catMember);
			}
		}
		else
			return false;
	}
	public function _getCatMember($cat)
	{
		$model = new Models_NewsCategory();
		$catChilds = $model->db->select('id')->where_in('parent_id',$cat)->where_not_in('id',$cat)->getcFieldArray();
		if(!empty($catChilds))
		{
			$catChild = implode(',', $catChilds);
			$this->catMember = $this->catMember.','.$catChild;
			return $this->_getCatMember($catChild);
		}
		else 
			return $this->catMember;		
	}

    public function deleteCat($catId)
    {
        if(!empty($catId))
        {
            if($this->db->where('id', $catId)->Delete())
            {
                $modelNews = new Models_News();
                //Delete News
                $modelNews->db->where('category_id',$catId)->Delete();
                //delete child cat
                $arrChilds = $this->getChildCat($catId);
                if(!empty($arrChilds))
                {
                    foreach($arrChilds as $childCat)
                    {
                        if($this->db->where('id', $childCat['id'])->Delete())
                        {
                            $this->deleteCat($childCat['id']);
                        }
                    }
                }
                return true;
            }
        }
        return false;
    }

    function getLinkNews($news)
    {
        $params = array();
        $arrCat = $this->getCatParent($news['category_id']);
        $i = 1;
        foreach($arrCat as $c)
        {
            $params['catname' . $i] = $c;
            $i++;
        }
        $params['name'] = string::seo($news['title']);
        $params['id'] = $news['id'];
        return $params;
    }

    /**
     * get max order_no by parentId
     */
    public static function getMaxOrderNo($parentid = 0)
    {
        $obj = self::getInstance();
        if($parentid)
            $obj->db->where('parent_id', $parentid);
        return $obj->db->select('max(orderno)')->getField();
    }
}







