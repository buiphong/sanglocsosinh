<?php
#[Table('news_relative')]
#[PrimaryKey('id')]
class Models_NewsRelative extends PTModel
{
    public $id;
    public $news_id;
    public $relative_news_id;
    public $order_no;
    public $created_uid;
    public $created_time;

    /**
     * Lấy danh sách tin liên quan theo tin bài
     */
    public static function getRelativeNews($newsId)
    {
        if(empty($newsId))
            return false;
        $obj = self::getInstance();
        $obj->db->join('news', 'news.id = news_relative.relative_news_id')->where('news_relative.news_id', $newsId);
        return $obj->db->select('news.id,news.title,news.url_title,news.published_date,news.category_id')->getcFieldsArray();
    }

    /**
     * Add tin liên quan
     */
    public static function addNews($listId, $newsId)
    {
        $obj = self::getInstance();
        //Get max order_no
        $maxOrder = self::getMaxOrder($newsId);
        if(!$maxOrder)
            $maxOrder = 0;
        $maxOrder = $maxOrder + 1;
        if(!is_array($listId) && strpos($listId, ',') !== false)
            $listId = explode(',', $listId);
        elseif(!is_array($listId))
            $listId = array($listId);
        if(is_array($listId))
        {
            foreach($listId as $v)
            {
                if(!empty($v))
                {
                    $data = array(
                        'news_id' => $newsId,
                        'relative_news_id' => $v,
                        'order_no' => $maxOrder,
                        'created_time' => date('Y-m-d H:i:s', time()),
                        'created_uid' => $_SESSION['pt_control_panel']["system_userid"]
                    );
                    if(!$obj->Insert($data))
                        return false;
                    $maxOrder ++;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Lấy vị trí lớn nhất hiện tại tin liên quan của một danh mục tin.
     * @param $newsId
     */
    public static function getMaxOrder($newsId)
    {
        $obj = self::getInstance();
        return $obj->db->select('max(order_no)')->where('news_id', $newsId)->getField();
    }
}