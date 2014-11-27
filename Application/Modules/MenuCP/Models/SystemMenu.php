<?php
#[Table('systemmenu')]
#[PrimaryKey('id')]
class Models_SystemMenu extends PTModel
{
	public $id;
	
	public $title;
	
	public $actionid;
	
	public $externallink;
	
	public $parentid;
	
	public $iconpath;
	
	public $languageid;
	
	#[DataType('number')]
	public $orderno;
	#[DataType('number')]
	public $status;
	#[Update(false)]
	public $type_id;

    public $icon_class;
	
	private $_listMenu;
	
	public function getMenuMultiLevel($parentId = 0, $conds)
	{
		foreach ($conds as $key => $value)
		{
			$this->db->where($key, $value);
		}
		$menu = $this->db->where('parentid', $parentId)->orderby('orderno')->getAll();
		foreach ($menu as $key => $item)
		{
			$childs = $this->getMenuMultiLevel($item['id'], $conds);
			if (!empty($childs))
				$menu[$key]['childs'] = $childs;
			$this->_listMenu[] = $menu;
		}
		return $menu;
	}

    public static function deleteChildMenu($parent)
    {
        $obj = self::getInstance();
        return $obj->db->where('parentid', $parent)->Delete();
    }
}