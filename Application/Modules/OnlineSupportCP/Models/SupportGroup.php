<?php
#[Table('online_support_group')]
#[PrimaryKey('id')]
class Models_SupportGroup extends VccModel
{
	#[Update(false)]
	public $id;
	
	public $group_name;
	
	public $desc;
	
	public $create_time;
	
	public $status;
	
	public $lang_code;
}