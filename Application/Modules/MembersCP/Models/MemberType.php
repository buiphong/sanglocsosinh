<?php
#[Table('member_type')]
#[PrimaryKey('id')]
class Models_MemberType extends PTModel
{
    public $id;
    public $name;
    public $desc;
}