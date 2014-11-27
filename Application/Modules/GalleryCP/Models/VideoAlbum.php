<?php
#[Table('gallery_video_album')]
#[PrimaryKey('id')]
class Models_VideoAlbum extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $name;
	
	public $desc;
	
	public $create_time;
	
	public $status;
	
	public $avatar;
	
	public $address;
	
	public $orderno;
	
	public $created_uid;
	
	public $parent_id;
	
	public $lang_code;
	
	private $_arrParent;
	
	private $_listCat = array();
	
	function getTreeAlbum($parentId = 0, $default = true, $langId = 'vi-VN', $id = 0)
	{
		$this->_listCat = array();
		if ($default)
			$this->_listCat['0'] = 'Danh mục gốc';
		return $this->_getTreeAlbum($parentId, '', $langId, $id);
	}
	private function _getTreeAlbum($parentId = 0, $prefix = '', $langId = 'vi-VN', $id = 0){
		if($langId)
			$this->db->where('lang_code', $langId);
		$albums = $this->db->select('id,name')->where('parent_id', $parentId)->where('id <>', $id)->getAll();
		if(!empty($albums)){
			foreach ($albums as $key => $album){
				$this->_listCat[$album['id']] = $prefix . $album['name'];
				$this->_getTreeAlbum($album['id'], $prefix . '----', $langId, $id);
			}
		}
		return $this->_listCat;
	}
	/**
	 * get array parent
	 */
	public function getArrayParent($catId){
		$this->_arrParent = array();
		$this->_getArrayParent($catId);
		return $this->_arrParent;
	}
	
	private function _getArrayParent($catId){
		if (!empty($catId)) {
			$parentId = $this->db->select('parent_id')->where('id', $catId)->getField();
			if ($parentId) {
				$album = $this->db->select('id,name,parent_id')->where('id', $parentId)->getFields();
				if (!empty($album)) {
					array_unshift($this->_arrParent, $album);
					$this->_getArrayParent($album['id']);
				}
			}
		}
		return $this->_arrParent;
	}
    private $_childAlbum;
    public function getChildAlbum($catId,$langCode = 'vi-VN')
    {
        $this->_childAlbum = array();
        $this->_getChildAlbum($catId);
        return $this->_childAlbum;
    }
    private function _getChildAlbum($catid,$langCode = 'vi-VN')
    {
        $childs = $this->db->select('id,name')->where('parent_id', $catid)->where('lang_code', $langCode)->getFieldsArray();
        if(!empty($childs))
        {
            foreach($childs as $child)
            {
                $this->_childAlbum[] = $child;
                $this->_getChildAlbum($child['id']);
            }
        }
    }
}
?>