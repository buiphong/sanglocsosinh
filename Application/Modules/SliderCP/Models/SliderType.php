<?php
#[Table('slider_type')]
#[PrimaryKey('id')]
class Models_SliderType extends PTModel
{
	#[Update(false)]
	public $id;
		
	public $name;
	
	public $desc;
	
	public $lang_code;
	
	public $order_no;
	
	public $status;
	
	public $create_time;
	
	public $create_uid;

    public $is_default;
	
	private $_arrType;
	
	public function getSliderType(){
		return $this->db->select('id,name')->orderby('order_no')->getAll();
	}
	/**
	 * get array parent
	 */
	public function getNameType($typeId){
		return $this->db->select('name')->where('id', $typeId)->getField();
	}
}