<?php
#[Table('online_support')]
#[PrimaryKey('id')]
class Models_OnlineSupport extends VccModel
{
	#[Update(false)]
	public $id;
	
	public $fullname;
	
	public $desc;
	
	public $contact_ids;
	
	public $group_id;
	
	public $create_time;
	
	public $status;
	
	public $lang_code;

    public $phone;

    public $image_path;
}