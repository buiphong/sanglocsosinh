<?php
#[Table('config')]
#[PrimaryKey('id')]
class Models_Config extends PTModel
{
	public $id;
	
	public $code;
	
	public $title;
	
	public $group_id;
}