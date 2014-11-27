<?php
#[Table('contacts')]
#[PrimaryKey('id')]
class Models_Contact extends PTModel
{
	#[Update('False')]
	public $id;
	public $fullname;
	public $phone;
	public $address;
	public $email;
	public $company;
	public $title;
	public $content;
	public $create_date;
	#[DataType('number')]
	public $status;
	public $lang_code;
	public $image_ad;
	public $website;

    /**
     * Đếm tổng số lượng liên hệ theo trạng thái
     */
    public static function countByStatus($status = '')
    {
        $obj = self::getInstance();
        if(!empty($status) || $status == 0)
            $obj->db->where('status', $status);
        return $obj->db->count(true);
    }

    /**
     * Cập nhật trạng thái liên hệ
     */
    public static function updateStatus($id, $status)
    {
        if(!empty($id) && (!empty($status) || $status == 0))
        {
            $obj = self::getInstance();
            $status = (int)$status;
            return $obj->db->where('id', $id)->update(array('status' => $status));
        }
        return false;
    }
}