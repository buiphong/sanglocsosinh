<?php
#[Table('gallery_image_album')]
#[PrimaryKey('id')]
class Models_ImageAlbum extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $name;
	
	public $desc;
	
	public $create_time;
	
	public $status;
	
	public $orderno;
	
	public $address;
	
	public $created_uid;
	
	public $parent_id;
	
	public $lang_code;

    public $avatar;

    public $category_id;
	
	private $_arrParent;
	private $_listCat = array();
	
	/**
	 * Lấy danh sách album dưới dạng cây
	 */
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
	public function getAlbumNext($id = null, $parent_id = null){
		if($parent_id == null)
			return $this->db->conn->GetRow("SELECT `id`,`name` From gallery_image_album Where `id` > $id and `status`=1 limit 0,1");
		else
			return $this->db->conn->GetRow("SELECT `id`,`name` From gallery_image_album Where `id` > $id and `parent_id` = $parent_id and `status`=1 limit 0,1");
	}
	public function getAlbumPrev($id = null, $parent_id = null){
		if($parent_id == null)
			return $this->db->conn->GetRow("SELECT `id`,`name` From gallery_image_album Where `id` < $id and  `status`=1 order by id desc limit 0,1");
		else
			return $this->db->conn->GetRow("SELECT `id`,`name` From gallery_image_album Where `id` < $id and `parent_id` = $parent_id and `status`=1 order by id desc limit 0,1");
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

    /**
     * Get album detail by id, include category's name
     */
    public static function getDetailAlbum($albumId)
    {
        $obj = self::getInstance();
        return $obj->db->select('gallery_image_album.*,gallery_image_album_category.name as category')
                ->join('gallery_image_album_category','gallery_image_album_category.id = gallery_image_album.category_id')
                ->where('gallery_image_album.id', $albumId)->getcFields();
    }

    /**
     * Get new Album
     */
    public static function getNewAlbum($limit=5)
    {
        $obj = self::getInstance();
        $albums = $obj->db->orderby('create_time', 'desc')->limit($limit)->getFieldsArray();
        return $albums;
    }

    public static function getAlbumByCat($catid, $limit, $page = 1)
    {
        $offset = ($page - 1) * $limit;
        $obj = self::getInstance();
        $albums = $obj->db->where('category_id', $catid)->orderby('create_time', 'desc')->limit($limit,$offset)->getFieldsArray();
        return $albums;
    }
}
?>