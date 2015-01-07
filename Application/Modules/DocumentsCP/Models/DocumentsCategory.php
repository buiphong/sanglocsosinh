<?php
#[Table('documents_category')]
#[PrimaryKey('id')]
class Models_DocumentsCategory extends PTModel{
	#[Update(false)]
	public $id;
	
	public $title;
	
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
	
	private $_listCat = array();
	
	private $_arrParent;

	/**
	 * Lấy danh sách danh mục dưới dạng cây
	 */
	function getTreeCategory($parentId=0, $default = true, $langId='vi-VN'){
		$this->_listCat = array();
		if ($default)
			$this->_listCat['0'] = 'Danh mục gốc';
		return $this->_getTreeCategory($parentId,'', $langId);
	}
	
	private function _getTreeCategory($parentId = 0, $prefix = '', $langId = 'vi-VN'){
		//Lấy danh sách danh mục
		$cats = $this->db->select('id,title')->where('parent_id', $parentId)->where('lang_code',$langId)->orderby('orderno')->getAll();
		if( !empty($cats)){
			foreach ($cats as $key => $cat){
				$this->_listCat[$cat['id']] = $prefix . $cat['title'];
				$this->_getTreeCategory($cat['id'], $prefix . '----', $langId);
			}
		}
		return $this->_listCat;
	}
	
	/**
	 * Lấy danh sách danh mục tin
	 */
	public function getListCatMultiLevel($parentId = 0, $langId = 'vi-VN'){
		return $this->_getListCatMultiLevel($parentId, $langId);
	}
	
	private function _getListCatMultiLevel($parentId = 0, $langId = 'vi-VN'){
        if(!empty($langId))
            $this->db->where('lang_code', $langId);
		$cats = $this->db->select('id,title,parent_id')->where('parent_id', $parentId)->getFieldsArray();
		if (!empty($cats)){
			foreach ($cats as $key => $cat){
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
	 * get array parent
	 */
	public function getArrayParent($catId)
	{
		$this->_arrParent = array();
		$this->_getArrayParent($catId);
		return $this->_arrParent;
	}
	
	private function _getArrayParent($catId){
		if (!empty($catId)) {
			$parentId = $this->db->select('parent_id')->where('id', $catId)->getField();
			if ($parentId) {
				$cat = $this->db->select('id,title,parent_id')->where('id', $parentId)->getFields();
				if (!empty($cat)) {
					array_unshift($this->_arrParent, $cat);
					$this->_getArrayParent($cat['id']);
				}
			}
		}
		return $this->_arrParent;
	}
	/*Lấy các danh mục cha  */
	private $arrCat = array();
	//private $catRoot;
	public function getCatParent($catId){
		$cat = self::getById($catId);
		if(empty($cat['parent_id']))
            return $cat;
        else
		    return $this->_getCatParent($cat);
	}
	public function _getCatParent($cat){
		$cat = self::getById($cat['parent_id']);
		if(!empty($cat['parent_id'])){
			return $this->getCatParent($cat);
		}
		else
			return $cat;
	}
	
	/*Danh mục dành cho thành viên */
	private $catMember;
	public function getCatMember()
	{
        $modelCat = new Models_DocumentsCategory();
        $cats = $modelCat->db->select('id')->where_not_in('is_member',-1)->getcFieldArray();
        if(!empty($cats))
        {
            $this->catMember = implode(',', $cats);
            return $this->_getCatMember($this->catMember);
        }
	}
	public function _getCatMember($cat)
	{
		$modelCat = new Models_DocumentsCategory();
		$catChilds = $modelCat->db->select('id')->where_in('parent_id',$cat)->where_not_in('id',$cat)->getcFieldArray();
		if(!empty($catChilds))
		{
			$catChild = implode(',', $catChilds);
			$this->catMember = $this->catMember.','.$catChild;
			return $this->_getCatMember($catChild);
		}
		else
			return $this->catMember;
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
        $childs = $this->db->select('id,title')->where('parent_id', $catid)->where('lang_code', $langCode)->getFieldsArray();
        if(!empty($childs))
        {
            foreach($childs as $child)
            {
                $this->_childCat[] = $child;
                $this->_getChildCat($child['id']);
            }
        }
    }
}