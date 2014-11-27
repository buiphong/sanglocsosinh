<?php
#[Table('documents')]
#[PrimaryKey('id')]
class Models_Documents extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $title;
	
	#[DataType('number')]
	public $category;
	
	public $number_sign;
	
	public $organ_promulgate;
	
	public $description;
	
	public $image;
	
	public $content;
	
	public $file_url;
	
	public $lang_code;
	
	#[DataType('number')]
	public $status;
	
	#[Update(false)]
	public $created_time;
	
	public $modified_time;

    public $published_time;

    public $file_info;

    public $member;

    public static function getDocByCat($cat, $limit, $cond)
    {
        $obj = self::getInstance();
        $where = '';
        if(!empty($cat))
        {
            $modelCat = new Models_DocumentsCategory();
            //get child cat
            $childs = $modelCat->getChildCat($cat);
            $where = '(category=' . $cat;
            foreach ($childs as $key => $title)
            {
                $where .= ' or category = ' . $key;
            }
            $where .= ')';
        }
        if(!empty($cond))
            $obj->db->where($cond);
        if($where)
            $obj->db->where($where);
        $doc = $obj->db->select('id,title,description,image,published_time,category,created_time')
            ->orderby('published_time', 'desc')->limit($limit)->getcFieldsArray();
        return $doc;
    }
}