<?php
#[Table('users')]
#[PrimaryKey('id')]
class Models_Login extends PTModel
{
	public $id;
	
	public $username;
	
	public $password;
	
	public $fullname;
	
	public $lastlogin;
	
	public $lastsession;
	
	public function checkLogin()
	{
		if (!empty($this->username) && !empty($this->password))
		{
			$password = md5($this->password);
			$result = $this->db->where('username',$this->username)->where('password', $password)->getFields();
			if ($result && $result['status'] != 0)
				return $result;
		}
		return false;
	}
}