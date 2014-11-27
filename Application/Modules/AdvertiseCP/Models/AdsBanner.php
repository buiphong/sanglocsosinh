<?php
#[Table('ads_banner')]
#[PrimaryKey('id')]
class Models_AdsBanner extends PTModel
{
	public $id;
	public $zone_id;
	public $name;
	public $width;
	public $height;
	public $status;
	public $link;
	public $file_data;
	public $banner_type;
	public $lang_code;
    public $desc;
    public $orderno;
    public $real_width;
    public $read_height;
}