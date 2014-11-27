<?php
#[Table('news_comment')]
#[PrimaryKey('id')]
class Models_NewsComment extends PTModel
{
	public $id;
	public $fullname;
	public $email;
	public $title;
	public $content;
	public $created_date;
	public $news_id;
	public $status;

    /**
     * Count comment by news_id
     */
    public function countCmt($nId)
    {
        if(!empty($nId))
        {
            $cmt = $this->db->where('news_id', $nId)->count(true);
            if(!empty($cmt))
                return $cmt;
        }
        return 0;
    }
}