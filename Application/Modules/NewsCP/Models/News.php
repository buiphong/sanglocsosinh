<?php
#[Table('news')]
#[PrimaryKey('id')]
class Models_News extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $title;
	
	public $url_title;

    public $meta_description;
	
	public $brief;
	
	public $content;
	
	public $image_path;
	
	public $video_path;
	
	public $category_id;
	
	#[DataType('number')]
	public $status;
	
	public $keywords;
	
	public $published_by;
	
	public $published_date;
	
	public $created_date;
	
	public $created_by;
	
	public $notes;
	
	public $editor_id;
	
	public $update_date;
	
	public $type_id;
	
	public $hascomment;
	
	public $rating;
	
	public $hits;
	
	public $lang_code;
	
	public $other_categories;
	
	public $author_id;
	
	public $hasindex;

    public function createVersion()
    {
        $mv = new Models_NewsVersion();
        $mv->news_id = $this->id;
        $mv->title = $this->title;
        $mv->url_title = $this->url_title;
        $mv->brief = $this->brief;
        $mv->content = $this->content;
        $mv->meta_description = $this->meta_description;
        $mv->image_path = $this->image_path;
        $mv->video_path = $this->video_path;
        $mv->category_id = $this->category_id;
        $mv->author_id = $this->author_id;
        $mv->status = $this->status;
        $mv->keywords = $this->keywords;
        $mv->published_by = $this->published_by;
        $mv->published_date = $this->published_date;
        $mv->created_date = $this->created_date;
        $mv->created_by = $this->created_by;
        $mv->notes = $this->notes;
        $mv->editor_id = $this->editor_id;
        $mv->type_id = $this->type_id;
        $mv->hascomment = $this->hascomment;
        $mv->rating = $this->rating;
        $mv->hits = $this->hits;
        $mv->lang_code = $this->lang_code;
        $mv->other_categories = $this->other_categories;
        $mv->hasindex = $this->hasindex;
        $mv->author_id = $this->author_id;
        $mv->ver_time = date('Y-m-d H:i:s');
        $mv->ver_uid = $_SESSION['pt_control_panel']["system_userid"];
        $mv->Insert();
    }

    /**
     * Get news, include category name
     */
    public static function getNewsById($newsId)
    {
        $obj = self::getInstance();
        return $obj->db->select('news.*,news_category.title as category')
            ->join('news_category', 'news.category_id = news_category.id')
            ->where('news.id', $newsId)->getcFields();
    }

    /**
     * Count search indexed news
     */
    public static function countHasIndex()
    {
        $obj = self::getInstance();
        return $obj->db->where('hasindex', 1)->count(true);
    }

    /**
     * get news by status
     */
    public static function countByStatus($status = '')
    {
        $obj = self::getInstance();
        if($status != '')
            $obj->db->where('status', $status);

        return $obj->db->count(true);
    }

    /**
     * Get list news by status
     */
    public static function getListNewsByStatus($status, $limit=0)
    {
        if($status)
        {
            $obj = self::getInstance();
            $obj->db->select('id,title,category_id,status,image_path,brief');
            $obj->db->where('status', $status);
            if($limit > 0)
                $obj->db->limit($limit);
            $list = $obj->db->getFieldsArray();
            return $list;
        }
        return false;
    }

    /**
     * Get list latest news
     */
    public static  function getListLatestNews($limit = 0)
    {
        $obj = self::getInstance();
        $obj->db->select("id, title, url_title, category_id, image_path, brief, published_date");
        $obj->db->where("status", 1)->orderby("published_date", "desc");
        if($limit > 0)
            $obj->db->limit($limit);
        $listNews = $obj->db->getFieldsArray();
        return $listNews;
    }

    public static function getNewsByCat($cat, $limit, $cond)
    {
        $obj = self::getInstance();
        $where = '';
        if(!empty($cat))
        {
            $modelCat = new Models_NewsCategory();
            //get child cat
            $childs = $modelCat->getChildCat($cat);
            $where = '(category_id=' . $cat;
            foreach ($childs as $key => $title)
            {
                $where .= ' or category_id = ' . $key;
            }
            $where .= ')';
        }
        $news = $obj->db->select('id,title,url_title,brief,image_path,published_date,category_id,created_date')
            ->where('news.status', 1)->where($cond)->where($where)
            ->orderby('created_date', 'desc')->limit($limit)->getcFieldsArray();
        return $news;
    }
}
