<?php
#[Table('menu')]
#[PrimaryKey('id')]
class Models_Menu extends PTModel
{
	public $id;
	
	public $title;
	
	public $categoryid;
	
	public $link_type;
	
	public $link_type_value;
	
	public $externallink;
	
	public $parentid;
	
	#[DataType('number')]
	public $orderno;
	
	#[DataType('number')]
	public $status;
	#[DataType('number')]
	public $isinhome;
	
	public $iconpath;
	
	public $iconpath_hover;
	
	public $template;
	
	public $layout;
	
	public $defaultskin;
	
	public $userid;
	
	#[Update(false)]
	public $type_id;
	
	public $portlet_id;
    
    public $lang_code;

    public $url_title;

    //Default menu
    public $is_default;

    public $target;

    public $desc;

    public $keywords;

    public $background_img;

    public $path;

    public static function deleteMenuByType($typeId)
    {
        $obj = self::getInstance();
        return $obj->db->where('type_id', $typeId)->Delete();
    }

    /**
     * Update menu position
     */
    public static function updatePosMenu($menuId, $pos, $parentId)
    {
        if(!empty($menuId) && !empty($pos))
        {
            $obj = self::getInstance();
            if($obj->db->where('id', $menuId)->update(array('orderno' => $pos, 'parentid' => $parentId)))
                return true;
            else
                return $obj->db->error;
        }
        return false;
    }

    public static function getMenuMultiLevel($typeId ,$langCode='vi-VN')
    {
        //Get all menu
        $obj = self::getInstance();
        if($typeId)
            $obj->db->where('type_id', $typeId);
        if(MULTI_LANGUAGE && $langCode)
            $obj->db->where('lang_code', $langCode);
        $cats = $obj->db->orderby('orderno')->getcFieldsArray();
        $arr = self::getChildMenu(0, $cats);
        return $arr;
    }

    private static function getChildMenu($parentId, $array)
    {
        $arr = array();
        foreach($array as $key => $item)
        {
            if($item['parentid'] == $parentId)
            {
                $arr[$item['id']]['values'] = $item;
                unset($array[$key]);
                $child = self::getChildMenu($item['id'], $array);
                if($child)
                    $arr[$item['id']]['subs'] = $child;
            }
        }
        return $arr;
    }

	/**
	 * Lấy danh sách menu
	 */
	public function getListMenuMultiLevel($typeId, $parentId = 0, $langId = 'vi-VN')
	{
		return $this->_getListNavMultiLevel($typeId, $parentId, $langId);
	}
	
	private function _getListNavMultiLevel($typeId, $parentId = 0, $langId = 'vi-VN')
	{
		if (!empty($typeId))
		{
			$model = new Models_Menu();
			$navs = $model->db->select('id,title,link_type,link_type_value,parent_id')
			->where('parent_id', $parentId)->where('type_id', $typeId)
			->where('lang_code', $langId)->orderby('orderno')->getFieldsArray();
			if (!empty($navs))
			{
				foreach ($navs as $key => $nav)
				{
					$navs[$key]['link'] = $this->url->action('index', array('parentid' => $nav['id']));
					$childs = $this->_getListNavMultiLevel($typeId, $nav['id'], $langId);
					if ($childs)
						$navs[$key]['subs'] = $childs;
				}
				return $navs;
			}
			else
				return false;
		}
		else
			return false;
	}

    private $_listMenu = array();

    /**
     * Lấy danh sách danh mục dưới dạng cây
     */
    function getTreeMenu($type, $parentId = 0, $default = true, $langId = '', $id = 0){
        $this->_listCat = array();
        if ($default)
            $this->_listMenu['0'] = 'Danh mục gốc';
        $arrCat = self::getMenuMultiLevel($type, $parentId, $langId);
        return $this->_getTreeMenu($arrCat, '', $langId, $id);
    }
    //Lấy danh sách danh mục
    private function _getTreeMenu($arr = array(), $prefix = '', $langId = '', $id = 0){
        if(!empty($arr)){
            foreach ($arr as $cat){
                $this->_listMenu[$cat['values']['id']] = $prefix . ' ' . $cat['values']['title'];
                if(isset($cat['subs']))
                    $this->_getTreeMenu($cat['subs'], $prefix . '----', $langId, $id);
            }
        }
        return $this->_listMenu;
    }

    private $parents;
    /**
     * Lấy danh sách danh mục cha
     */
    public function getArrayParent($id)
    {
        $this->parents = array();
        $this->_getArrayParent($id);
        return $this->parents;
    }
    public function _getArrayParent($id)
    {
        $menu = $this->db->select('id,title,parentid,url_title,externallink')->where('id', $id)->getcFields();
        if($menu)
        {
            array_unshift($this->parents,$menu);
            if(!empty($menu['parentid']))
            {
                $this->_getArrayParent($menu['parentid']);
            }
        }
    }

    public function getTemplateMenu($menuId)
    {
        if (!empty($menuId))
        {
            $menu = $this->db->where('id', $menuId)->getcFields();
            if (!empty($menu))
            {
                return array('template' => $menu['template'], 'layout' => $menu['layout']);
            }
        }
        return false;
    }

    /**
     * lấy danh sách menu theo loại menu
     * @param $typeId - Loại menu
     * @param $level - lựa chọn level để tạo menu (1 cấp, 2 cấp....)
     */
    public function getMenu($typeId, $parentId = 0)
    {
        $lang = (isset($_SESSION['langcode']))?$_SESSION['langcode']:'vi-VN';
        $this->db->where('lang_code', $lang);

        $menus = $this->db->select('id,title,target,url_title,externallink,iconpath,iconpath_hover,parentid')
            ->where('type_id', $typeId)->where('parentid', $parentId)
            ->orderby('orderno','asc')->getcFieldsArray();
    }

    /**
     * Lấy danh sách menu theo status
     */
    public static function getMenuByStatus($status = 0, $limit = 0)
    {
        $obj = self::getInstance();
        if($status)
            $obj->db->where('status', $status);
        if($limit > 0)
            $obj->db->limit($limit);
        return $obj->db->getFieldsArray();
    }

    /**
     * Lấy menu theo url
     */
    public static function getMenuByUrl($uri)
    {
        $obj = self::getInstance();
        return $obj->db->select('id,title,parentid,externallink,path')->like('externallink', $uri, 'before')->getFields();
    }
}