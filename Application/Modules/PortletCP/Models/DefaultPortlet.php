<?php
#[Table('default_layout_portlet')]
#[PrimaryKey('id')]
class Models_DefaultPortlet extends PTModel
{
	public $id;
	
	public $template;
	
	public $layout;
	
	public $region;
	
	public $portlet_id;
	
	public $values;
	
	public $orderno;
	
	public $params;
	
	public $lang_code;
	
	public $type;
}