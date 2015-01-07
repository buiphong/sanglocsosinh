<?php
#[Table('faq')]
#[PrimaryKey('id')]
class Models_Faqs extends PTModel
{
	#[Update('False')]
	public $id;
	public $question;
	public $answer;
	public $created_time;
	#[DataType('number')]
	public $status;
	#[DataType('number')]
	public $orderno;
	public $fullname;
    public $category_id;
	public $email;
	public $phone;
    public $member_id;
    public $hits;

    public static function updateHits($id)
    {
        if($id)
        {
            //Check cookie
            $data = @$_SESSION['slss_hit_faq'];
            if(!$data)
                $data = array();
            else
                $data = unserialize($data);
            if(!in_array($id, $data))
            {
                $result = self::runSQL("update faq set hits=hits+1 where id=$id");
                if($result)
                {
                    $data[] = $id;
                    $_SESSION['slss_hit_faq'] = serialize($data);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Kiểm tra có câu hỏi nào chưa được trả lời hay không
     */
    public static function checkUnAnswer()
    {
        $obj = self::getInstance();
        $result = $obj->db->select('count(id}')->where('status', 0)->or_where('answer', '')->getField();
        if($result && $result > 0)
            return $result;
        else
            return false;
    }

    public static function getMaxOrder($catId)
    {
        $model = self::getInstance();
        return $model->db->select('max(orderno)')->where('category_id',$catId)->getField();
    }
}