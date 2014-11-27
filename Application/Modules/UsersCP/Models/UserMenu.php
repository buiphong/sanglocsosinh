<?php
#[Table('user_menu')]
#[PrimaryKey('id')]
class Models_UserMenu extends PTModel
{
	public $id;
	public $userid;
	public $menuid;
}