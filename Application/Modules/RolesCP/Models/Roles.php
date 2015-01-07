<?php
#[Table('roles')]
#[PrimaryKey('id')]
class Models_Roles extends PTModel
{
	#[Insert('false')]
	public $id;
	public $name;
	public $description;
	public $status;
	public $actions;
	public $menu;
	public $rolelevel;
}