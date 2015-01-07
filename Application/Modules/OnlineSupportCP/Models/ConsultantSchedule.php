<?php
#[Table('consultant_schedule')]
#[PrimaryKey('id')]
class Models_ConsultantSchedule extends VccModel
{
	public $id;
	public $fullname;
	public $address;
    public $phone;
    public $email;
    public $consultant_type;
    public $time_consultant;
    public $status;
    public $member_id;
    public $mem_num;
    public $desc;
    public $bionet_flg;
    public $create_time;
}
