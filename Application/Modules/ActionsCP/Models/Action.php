<?php
#[Table('actions')]
#[PrimaryKey('id')]
class Models_Action extends PTModel
{
	public $id;
	public $code;
	public $name;
	public $description;
	public $groupid;
	public $status;
	public $controller;
	public $relatedfunctions;
	public $module;
	public $action;
}