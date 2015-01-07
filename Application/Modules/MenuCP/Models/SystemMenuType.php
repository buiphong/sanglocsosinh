<?php
#[Table('system_menu_type')]
#[PrimaryKey('id')]
class Models_SystemMenuType extends PTModel
{
	public $id;
	
	public $type_name;
	
	public $parentid;
	
	public $type_desc;
}