<?php
#[Table('gallery_video')]
#[PrimaryKey('id')]
class Models_Videos extends PTModel
{
	#[Update(false)]
	public $id;
	
	public $name;
	
	public $desc;
	
	public $img_path;
	
	public $orderno;
	
	public $album_id;
	
	public $status;
	
	public $video_path;
	
	public $create_time;
	
	public $created_uid;
	
	public $lang_code;

    /**
     * get video by album
     */
    public static function getVideosByAlbum($albumId='', $limit = 12, $page = 1)
    {
        $obj = self::getInstance();
        $offset = ($page-1)*$limit;
        if($albumId)
            $obj->db->where('album_id', $albumId);
        return $obj->db->orderby('orderno')->where('status', 1)->limit($limit, $offset)->getcFieldsArray();
    }
}