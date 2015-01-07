<?php
#[Table('gallery_images')]
#[PrimaryKey('id')]
class Models_Images extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $name;
	
	public $desc;
	
	public $create_time;
	
	public $file_path;
	
	public $created_uid;
	
	public $album_id;
	
	public $lang_code;
	
	public $orderno;
}