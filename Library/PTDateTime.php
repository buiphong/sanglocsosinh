<?php
/**
 * Lớp hỗ trợ xử lý datetime
 * @author buiphong
 *
 */
class PTDateTime
{
	/**
	 * Chuyển thời gian sang giây. Thời gian có định dạng: Y-m-d H:i:s
	 * @param string $datetime
	 */
	public static function toTime($datetime)
	{
		$hour = array(0 => 0, 1 => 0, 2 => 0);
		$dateMonth = array(0 => 0, 1 => 0, 2 => 0);
		$arr = explode(' ', $datetime);
		if (is_array($arr) && sizeof($arr) > 1) {
			// chuyen doi "H:i:s" sang giay
			$hour = explode(':', $arr[1]);
			$dateMonth = explode('-', $arr[0]);
		} else {
			$dateMonth = explode('-', $datetime);
		}
		try {
			return mktime($hour[0], $hour[1], $hour[2], $dateMonth[1],
					$dateMonth[2], $dateMonth[0]);
		} catch (Exception $e) {
			return 0;
		}
	}
	
	/** Ham convert thong tin bao nhieu ngay, gio, thang, nam
	 *
	 */
	public static function convertFormatTime ($sub, $arrFormat = '')
	{
		$strTime = '';
		if (! is_array($arrFormat) or count($arrFormat) <= 0)
			$arrFormat = array(0 => 'giây', 1 => 'phút', 2 => 'giờ', 3 => 'ngày',
					4 => 'tháng', 5 => 'năm');
		if (round($sub / (24 * 60 * 60 * 365)) >= 1) // be hon 1 nam
			$strTime .= round($sub / (24 * 60 * 60 * 365)) . ' ' .
			$arrFormat[5];
		else
			if (round($sub / (24 * 60 * 60 * 30 * 12)) < 1 and
					round($sub / (24 * 60 * 60 * 30)) >= 1) // be hon 1 nam, lon hon 1 thang
			$strTime .= round($sub / (24 * 60 * 60 * 30)) .
			' ' . $arrFormat[4];
		else
			if (round($sub / (24 * 60 * 60)) >= 1 and
					round($sub / (24 * 60 * 60 * 30)) < 1) // be hon 1 thang, lon hon 1 ngay
			$strTime .= round($sub / (24 * 60 * 60)) .
			' ' . $arrFormat[3];
		else
			if (round($sub / (60 * 60)) >= 1 and
					round($sub / (24 * 60 * 60)) < 1) // be hon 1 ngay, lon hon 1 gio
			$strTime .= round($sub / (60 *
					60)) . ' ' . $arrFormat[2];
		else
			if (round($sub / 60) >= 1 and round($sub / (60 * 60)) < 1) // be hon 1 gio
			$strTime .= round(
					$sub / 60) . ' ' . $arrFormat[1];
		else
			if (round($sub / 60) < 1) // be hon 1 phut
			$strTime .= $sub .
			' ' . $arrFormat[0];
		return $strTime;
	}
	
	/**
	 * get day of week (Y-m-d H:i:s)
	 * @param $type: full/short
	 */
	public static function getDayOfWeek($date, $type='full') {
		$value = date('w', strtotime($date));
		if ($type == 'full')
			switch ($value) {
				case 0 :
					$value = 'Chủ nhật';
					break;
				case 1 :
					$value = 'Thứ hai';
					break;
				case 2 :
					$value = 'Thứ ba';
					break;
				case 3 :
					$value = 'Thứ tư';
					break;
				case 4 :
					$value = 'Thứ năm';
					break;
				case 5 :
					$value = 'Thứ sáu';
					break;
				case 6 :
					$value = 'Thứ bảy';
					break;
		}
		elseif ($type == 'short')
		switch ($value) {
			case 0 :
				$value = 'CN';
				break;
			case 1 :
				$value = 'T.2';
				break;
			case 2 :
				$value = 'T.3';
				break;
			case 3 :
				$value = 'T.4';
				break;
			case 4 :
				$value = 'T.5';
				break;
			case 5 :
				$value = 'T.6';
				break;
			case 6 :
				$value = 'T.7';
				break;
		}
		return $value;
	}
	
	/**
	 * Lấy tên tháng theo tiếng việt
	 * @param int $month
	 * @param string $type - long/short
	 */
	public static function getNameMonth($month, $type = 'long')
	{
		if ($type == 'long')
		{
			switch (intval($month)) {
				case 1 :
					$value = 'Tháng 01';
					break;
				case 2 :
					$value = 'Tháng 02';
					break;
				case 3 :
					$value = 'Tháng 03';
					break;
				case 4 :
					$value = 'Tháng 04';
					break;
				case 5 :
					$value = 'Tháng 05';
					break;
				case 6 :
					$value = 'Tháng 06';
					break;
				case 7 :
					$value = 'Tháng 07';
					break;
				case 8 :
					$value = 'Tháng 08';
					break;
				case 9 :
					$value = 'Tháng 09';
					break;
				case 10 :
					$value = 'Tháng 10';
					break;
				case 11 :
					$value = 'Tháng 11';
					break;
				case 12 :
					$value = 'Tháng 12';
					break;
			}
		}
		elseif ($type == 'short')
		{
			switch (intval($month)) {
				case 1 :
					$value = 'T.01';
					break;
				case 2 :
					$value = 'T.02';
					break;
				case 3 :
					$value = 'T.03';
					break;
				case 4 :
					$value = 'T.04';
					break;
				case 5 :
					$value = 'T.05';
					break;
				case 6 :
					$value = 'T.06';
					break;
				case 7 :
					$value = 'T.07';
					break;
				case 8 :
					$value = 'T.08';
					break;
				case 9 :
					$value = 'T.09';
					break;
				case 10 :
					$value = 'T.10';
					break;
				case 11 :
					$value = 'T.11';
					break;
				case 12 :
					$value = 'T.12';
					break;
			}
		}
		else
			return false;
		return $value;
	}
	
	public static function formatDate($str, $frm="dd/mm/yyyy"){
		$str = str_replace("-", "/", $str);
		$tmpArr = explode("/", $str);
		if (count($tmpArr) == 2 && $frm == 'dd/mm/yyyy')
		{
			return '0000-' . $tmpArr[1] . '-' . $tmpArr[0];
		}
		if (count($tmpArr)<>3) return "";
		$tmpY = 0; $tmpM = 0; $tmpD = 0;
		switch ($frm){
			case "dd/mm/yyyy":
				{
					$tmpD = $tmpArr[0]; $tmpM = $tmpArr[1]; $tmpY = $tmpArr[2]; break;
				}
			case "mm/dd/yyyy": 
				{
					$tmpD = $tmpArr[1]; $tmpM = $tmpArr[0]; $tmpY = $tmpArr[2]; break;
				}
			case "yyyy/mm/dd": 
				{
					$tmpD = $tmpArr[2]; $tmpM = $tmpArr[1]; $tmpY = $tmpArr[0]; break;
				}
		}
		if (!checkdate($tmpM, $tmpD, $tmpY)) return "";
		return date('Y-m-d', strtotime($tmpY."-".$tmpM."-".$tmpD));
	}
	
	/**
	 * Format định dạng thời gian về yy-mm-dd H:i:s
	 */
	public static function formatDateTime($str, $frm='dd/mm/yyyy H:i:s')
	{
        $str = str_replace("-", "/", $str);
		$datetimeArr = explode(' ', $str);
		if (count($datetimeArr) == 1) {
			return VccDateTime::formatDate($datetimeArr[0]);
		}
		elseif (count($datetimeArr) > 1)
		{
            if($frm == 'H:i:s dd/mm/yyyy')
            {
                $date = VccDateTime::formatDate($datetimeArr[1]);
                $time = $datetimeArr[0];
            }
            elseif($frm == 'dd/mm/yyyy H:i:s')
            {
                $date = VccDateTime::formatDate($datetimeArr[0]);
                $time = $datetimeArr[1];
            }
			return $date . ' ' . $time;
		}
	}
	
	public static function userDate($str){
		$str = str_replace("-", "/", $str);
		$dArr = explode(" ", $str); $str=$dArr[0];
		$tmpArr = explode("/", $str);
		if (count($tmpArr)<>3) return "";
		$tmpY = 0; $tmpM = 0; $tmpD = 0;
		/* $drv = DRIVER;
		switch ($drv){
			case "mssql": $tmpD = $tmpArr[2]; $tmpM = $tmpArr[1]; $tmpY = $tmpArr[0]; break;
			case "mysql": $tmpD = $tmpArr[2]; $tmpM = $tmpArr[1]; $tmpY = $tmpArr[0]; break;
		} */
		$tmpD = $tmpArr[2]; $tmpM = $tmpArr[1]; $tmpY = $tmpArr[0];
		if ($tmpY == '0000')
			return $tmpD."/".$tmpM;
		else
		{
			if (!checkdate($tmpM, $tmpD, $tmpY)) return "";
			return $tmpD."/".$tmpM."/".$tmpY;
		}
	}
	
	/**
	 * format to user datetime
	 */
	public static function userDateTime($str)
	{
		$dateTimeArr = explode(' ', $str);
		$date = PTDateTime::userDate($dateTimeArr[0]);
		return $date . ' ' . $dateTimeArr[1];
	}
	
	/**
	 * Hiển thị text time
	 */
	public static function toTextTime($fromTime, $toTime, $type = 'long')
	{
		if (!empty($fromTime) && !empty($toTime))
		{
			$text = '';
			if (date('Y-m-d', strtotime($fromTime)) == date('Y-m-d', strtotime($toTime)))
			{
				//Trong cùng một ngày: hiển thị theo thời gian
				if (date('H:i', strtotime($fromTime)) < date('H:i', strtotime($toTime)) && date('H:i', strtotime($toTime)) > '00:00')
				{
					$arrTime = explode(':', date('H:i', strtotime($fromTime)));
					if ($arrTime[1] > '00')
						$fTime = $arrTime[0] . 'h' . $arrTime[1];
					else
						$fTime = $arrTime[0] . 'h';
						
					$arrTime = explode(':', date('H:i', strtotime($toTime)));
					if ($arrTime[1] > '00')
						$tTime = $arrTime[0] . 'h' . $arrTime[1];
					else
						$tTime = $arrTime[0] . 'h';
					if ($type == 'long')
						$text = 'Từ ' . $fTime . ' đến ' . $tTime;
					elseif ($type == 'short')
						$text = $fTime . ' - ' . $tTime;
				}
			}
			return $text;
		}
		else
			return false;
	}
	
	/**
	 * Hiển thị text thời gian
	 */
	public static function toTextDate($fromTime, $toTime)
	{
		if (!empty($fromTime) && !empty($toTime))
		{
			$text = '';
			if (date('Y-m-d', strtotime($fromTime)) == date('Y-m-d', strtotime($toTime)))
			{
				//Trong cùng một ngày: hiển thị theo thời gian
				if (date('H:i', strtotime($fromTime)) < date('H:i', strtotime($toTime)) && date('H:i', strtotime($toTime)) > '00:00')
				{
					$arrTime = explode(':', date('H:i', strtotime($fromTime)));
					if ($arrTime[1] > '00')
						$fTime = $arrTime[0] . 'h' . $arrTime[1];
					else
						$fTime = $arrTime[0] . 'h';
					
					$arrTime = explode(':', date('H:i', strtotime($toTime)));
					if ($arrTime[1] > '00')
						$tTime = $arrTime[0] . 'h' . $arrTime[1];
					else
						$tTime = $arrTime[0] . 'h';
					
					$text = 'Từ ' . $fTime . ' đến ' . $tTime . ' ngày ' . date('d/m/Y', strtotime($fromTime));
				}
				else
					$text = 'Ngày ' . date('d/m/Y', strtotime($fromTime));
			}
			elseif (date('Y-m-d', strtotime($fromTime)) < date('Y-m-d', strtotime($toTime)))
			{
				//Check time
				$fTime = date('H:i', strtotime($fromTime));
				if ($fTime > '00:00')
				{
					$arrTime = explode(':', $fTime);
					if ($arrTime[1] > '00')
						$time = $arrTime[0] . 'h' . $arrTime[1];
					else
						$time = $arrTime[0] . 'h';
					$text = 'Từ ' . $time . ' ngày ' . date('d/m/Y', strtotime($fromTime));
				}
				else
					$text = 'Ngày ' . date('d/m/Y', strtotime($fromTime));
				
				$tTime = date('H:i', strtotime($toTime));
				if ($tTime > '00:00')
				{
					$arrTime = explode(':', $tTime);
					if ($arrTime[1] > '00')
						$time = $arrTime[0] . 'h' . $arrTime[1];
					else
						$time = $arrTime[0] . 'h';
					$text .= ' tới ' . $time . ' ngày ' . date('d/m/Y', strtotime($toTime));
				}
				else
					$text .= ' tới ngày ' . date('d/m/Y', strtotime($toTime));
			}
			return $text;
		}
		else
			return false;
	}

    /**
     * Đưa ra ngày tháng hiển thị theo dạng facebook style
     * Nguồn: http://www.lkdeveloper.com/facebook-style-date-in-php-date/
     * @param type $timestamp
     * @return type
     */
    static public function RelativeTime2($timestamp){
        $difference = time() - $timestamp; // Make different between this time and time value which pass throw $timestamp
        $periods = array("giây", "phút", "giờ", "ngày", "tuần", "tháng", "năm", "thập kỉ");
        $lengths = array("60","60","24","7","4.35","12","10");
        if($difference < 2*24*60*60){
            if ($difference >= 0) { // this was in the past
                $ending = "trước";
            } else { // this was in the future
                $difference = -$difference;
                $ending = "tới";
            }
            for($j = 0; $difference >= $lengths[$j]; $j++) $difference /= $lengths[$j];
            $difference = round($difference);
            if($difference != 1) $periods[$j].= "";
            $text = "$difference $periods[$j] $ending";
            return $text;
        }else{
            return date('d/m/Y',$timestamp);
        }
    }
}