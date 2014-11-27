<?php
#[Table('news_special')]
#[PrimaryKey('id')]
class Models_NewsSpecial extends PTModel
{
	public $id;
	#[DataType('number')]
	public $news_id;
	public $title;
	public $url_title;
	public $brief;
	public $image_path;
	#[DataType('number')]
	public $category_id;
	#[DataType('number')]
	public $lang_code;
	#[DataType('number')]
	public $orderno;
	#[DataType('number')]
	public $special_type;
	public $published_date;

    /**
     * Get list special news
     */
    public static function getListNews($typeId, $limit = 0)
    {
        $modelNewsS = self::getInstance();
        if(empty($limit))
            $limit = 7;
        if(MULTI_LANGUAGE && $_SESSION['langcode'])
            $modelNewsS->db->where('news_special.lang_code', $_SESSION['langcode']);

        $newsS = $modelNewsS->db->select('news.*')->join('news',"news.id = news_special.news_id")->
            where('special_type',$typeId)->where('news.status', 1)->orderby('orderno','asc')->limit($limit)->getcFieldsArray();
        return $newsS;
    }

    /**
     * Sắp xếp lại danh sách tin sắp xếp
     */
    public static function reOrder($typeId)
    {
        $obj = self::getInstance();
        $list = $obj->db->select('id,orderno')->where('special_type', $typeId)->orderby('orderno', 'asc')->getFieldsArray();
        if($list)
        {
            $i = 1;
            foreach($list as $item)
            {
                $obj->db->where('id', $item['id'])->update(array('orderno' => $i));
                $i++;
            }
        }
        return false;
    }
}