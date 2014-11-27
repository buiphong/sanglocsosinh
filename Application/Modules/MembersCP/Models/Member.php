<?php
#[Table('member')]
#[PrimaryKey('id')]
class Models_Member extends PTModel
{
	public $id;
	public $fullname;
	public $username;
	public $password;
	public $gender;
	public $email;
	public $created_date;
	public $status;
	public $departments;
	public $birth_date;
    public $type_id;
    public $is_vip;
    public $phone;
    public $address;
    public $public;
    public $province_id;
    //public $member_code;

	public function checkLogin()
	{
		if (!empty($this->username) && !empty($this->password))
		{
			$this->username;
			$password = md5($this->password);
            if(!empty($this->type_id))
                $this->db->where('type_id',$this->type_id);
			$result = $this->db->where('username',$this->username)->where('password', $password)->getFields();
			if ($result)
				return $result;
		}
		return false;
	}

    /**
     * Đếm tổng số thành viên
     */
    public static function countMember()
    {
        $obj = self::getInstance();
        return $obj->db->count(true);
    }
}