<?php
#[Table('portletgroups')]
#[PrimaryKey('id')]
class Models_PortletGroup extends PTModel
{
	public $id;

	public $name;

	public $languageid;

	public $description;
}