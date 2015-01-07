<?php
#[Table('partners')]
#[PrimaryKey('id')]
class Models_Partner extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $name;
	
	public $desc;
	
	public $link;
	
	public $image;
	
	#[DataType('number')]
	public $orderno;
	
	public $lang_code;
}