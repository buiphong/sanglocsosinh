<?php
#[Table('news_special_type')]
#[PrimaryKey('id')]
class Models_NewsSpecialType extends PTModel
{
	public $id;
	public $code;
	public $title;
	public $lang_code;
}