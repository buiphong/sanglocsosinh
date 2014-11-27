<?php
#[Table('member_profile')]
#[PrimaryKey('id')]
class Models_MemberProfile extends PTModel
{
    public $id;
    public $member_id;
    public $division;
    public $knowledge;
    public $certificate;
    public $profile;
    public $public;
    public $created_date;
    public $edited_date;
}