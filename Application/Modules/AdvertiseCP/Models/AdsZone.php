<?php
#[Table('ads_zone')]
#[PrimaryKey('id')]
class Models_AdsZone extends PTModel
{
	public $id;
	public $zone_type;
	public $name;
	public $desc;
	public $cost;
	public $width;
	public $height;
	public $category_id;
	public $lang_code;
}