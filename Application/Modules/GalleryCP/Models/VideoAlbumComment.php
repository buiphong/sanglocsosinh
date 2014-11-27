<?php
#[Table('video_album_comment')]
#[PrimaryKey('id')]
class Models_VideoAlbumComment extends PTModel
{
	public $id;
	public $fullname;
	public $email;
	public $title;
	public $content;
	public $created_date;
	public $album_id;
	public $status;
}