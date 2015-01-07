<?php
#[Table('slider')]
#[PrimaryKey('id')]
class Models_Slider extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $title;
	
	public $name;
	
	public $desc;
	
	public $image;
	
	#[DataType('number')]
	public $orderno;
	
	public $link;
	
	public $status;
	
	public $type_id;
	
	public $create_time;
	
	public $create_uid;
	
	public $lang_code;

    function getDataByType($sliderType = '')
    {
        //if(MULTI_LANGUAGE && $_SESSION['langcode'])
        //    $this->db->where('lang_code', $_SESSION['langcode']);
        if(!empty($sliderType))
            $this->db->where('type_id',$sliderType);
        $arrSliders =  $this->db->select('id,title,name,link,image,orderno,desc')
            ->orderby('orderno')->getFieldsArray();
        return $arrSliders;
    }

    /**
     * delete slider by type
     */
    public static function delSliderByType($type)
    {
        if($type)
        {
            $obj = self::getInstance();
            return $obj->db->where('type_id', $type)->Delete();
        }
        return false;
    }
}