<?php
#[Table('news_version')]
#[PrimaryKey('id')]
class Models_NewsVersion extends PTModel
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

    public $ver_time;

    public $ver_uid;

    public $news_id;
}














