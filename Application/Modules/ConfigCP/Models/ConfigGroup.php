<?php
#[Table('config_group')]
#[PrimaryKey('id')]
class Models_ConfigGroup extends PTModel
{
	public $id;
	
	public $name;
	
	public $desc;
}