<?php
#[Table('index_management')]
#[PrimaryKey('id')]
class Models_IndexManagement extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $title;
	
	public $description;
	
	public $status;
	
	public $user_id;
	
	public $created;
	
	public $modified;
	
	public static function updateStatus($id, $status = 1){
        $obj = self::getInstance();
		$result = $obj->db->where("id", $id)->update(array("status"=>$status, "modified"=>date("Y-m-d H:i:s", time())));
	}
}