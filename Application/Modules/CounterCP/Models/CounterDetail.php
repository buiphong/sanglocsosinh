<?php
#[Table('counter_detail')]
#[PrimaryKey('id')]
class Models_CounterDetail extends PTModel{
	#[Update(false)]
	public $id;
	
	public $count_date;
	
	public $visits;
	
	public $visits_detail;

    public static function updateCounter()
    {
        //Check for session
        if(!isset($_SESSION['hasCounter']))
        {
            $obj = self::getInstance();
            $currCounter = $obj->db->where('count_date', date('Y-m-d', time()))->getFields();
            if($currCounter)
            {
                //Update
                $data = array('visits' => $currCounter['visits'] + 1);
                $obj->db->where('id', $currCounter['id'])->update($data);
            }
            else
            {
                //Insert
                $data = array('count_date' => date('Y-m-d', time()), 'visits' => 1);
                $obj->Insert($data);
            }
            //Update total visit
            Models_CounterDetail::runSQL("update website_data set `value`= `value` + 1 where code='visit'");
            $_SESSION['hasCounter'] = 1;
        }
        return true;
    }

    public static function getCounterByDate($date = '')
    {
        if(empty($date))
            $date = date('Y-m-d');
        $obj = self::getInstance();
        return $obj->db->where('count_date', $date)->getFields();
    }
}