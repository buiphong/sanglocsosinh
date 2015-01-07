<?php
#[Table('portlet_params')]
#[PrimaryKey('id')]
class Models_PortletParam extends PTModel
{
	public $id;

	public $portlet_id;

	public $title;

	public $name;

	public $type;

	public $options;

	public $param_sql;

	public $multivalued;
	
	public $desc;
}