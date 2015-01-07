<?php
#[Table('users')]
#[PrimaryKey('id')]
class Models_User extends PTModel
{
	public $id;
	
	public $username;
	
	public $password;
	
	public $fullname;
	
	public $user_type;
	
	public $permission;
	
	public $moreactions;
	
	public $status;
	
	public $createddate;
	
	public $lastlogin;
	
	public $lastsession;
	
	public $email;
	
	public $description;
	
	public $roles;
	
	public $avatar;
	
	public $slogan;
	
	public $emptype_id;
	
	public $newsRole;

    public $menuid;
}