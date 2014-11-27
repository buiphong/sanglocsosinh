<?php
#[Table('member_profile_field')]
#[PrimaryKey('id')]
class Models_MemTypeField extends PTModel
{
    public $id;
    public $memtype_id;
    public $field_name;
    public $field_type;
    public $input_type;
    public $field_code;
    public $desc;
    public $orderno;
    public $field_type_value;
}