<?php
#[Table('actiongroups')]
#[PrimaryKey('id')]
class Models_ActionGroup extends PTModel
{
	public $id;
	public $name;
	public $description;
	public $status;
}