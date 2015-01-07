<?php
/**
 * Lớp hỗ trợ các vấn đề về chuỗi
 * @author		buiphong
 */
class String
{
    /**
     * Mã hóa MD5 cho chuỗi theo quy tắc riêng
     * @param string $str Chuỗi cần mã hóa
     */
    public static function md5Encode ($str)
    {
        return md5(md5($str . "vcc.v3"));
    }
    
    /**
     * Mã hóa MD5 cho chuỗi kèm theo tham số
     * @param string $str Chuỗi cần mã hóa
     */
    public static function md5WithSaltEncode ($str, $salt)
    {
    	return md5(md5($str) . $salt);
    }

    /**
     * encode string base on 'base64_encode'
     */
    public static function encodeID($id)
    {
        return self::alphaID($id);
    }

    /**
     * decode string
     */
    public static function decodeID($id)
    {
        return self::alphaID($id, true);
    }
    
    /**
     * Phương thức tạo chuỗi ngẫu nhiên
     * @param int $num Số kí tự của chuỗi
     */
    public static function randomString ($num)
    {
        $characters = 'QWERTYUIOPLKJHGFDSAZXCVBNM0123456789qwertyuioplkjhgfdsazxcvbnm';
        $string = '';
        for ($p = 0; $p < $num; $p ++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
    public static function secure ($s_str)
    {
        if((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
           || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != "off")))
        {
            if(is_array($s_str))
            {
                foreach($s_str as $k => $v) $s_str[$k] = stripslashes($v);
            }
            else
                $s_str = stripslashes($s_str);
        }
        //HtmlSpecialCharsEncode
        //$s_str = self::HtmlStringEncode($s_str);
        //Script, blocked
        //$s_str = str_ireplace("script", "blocked", $s_str);
        //Sql inject
        //$s_str = mysql_escape_string($s_str);
        return $s_str;
    }
    public static function HtmlStringEncode ($html)
    {
        $out = htmlspecialchars(html_entity_decode($html, ENT_QUOTES, 'UTF-8'), 
        ENT_QUOTES, 'UTF-8');
        return $out;
    }
    public static function HtmlStringDecode ($html)
    {
        $out = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        return $out;
    }
    public static function HtmlEncode ($html)
    {
        return htmlentities($html);
    }
    public static function HtmlDecode ($html)
    {
        return html_entity_decode($html);
    }
    public static function trim_all ($string)
    {
        $arrChars = array("\t", "\n", "\r");
        foreach ($arrChars as $c) {
            $string = str_replace($c, "", $string);
        }
        return $string;
    }
    public static function removeSign ($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        return $str;
    }
    public static function seo ($str)
    {
        $str = strtolower(self::removeSign($str));
        $str = str_replace("&*#39;", "", $str);
        $str = str_replace(" ", "-", $str);
		$str = str_replace("…", "", $str);
        $str = str_replace(":", "-", $str);
        $str = str_replace(".", "-", $str);
        $str = str_replace(",", "-", $str);
        $str = str_replace("%", "", $str);
        $str = str_replace("/", "-", $str);
        $str = str_replace("\\", "-", $str);
        $str = str_replace("?", '', $str);
        $str = str_replace("&", "", $str);
        $str = str_replace('"', "", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace("’", "", $str);
        $str = str_replace("(", "", $str);
        $str = str_replace(")", "", $str);
        $str = str_replace("”", "", $str);
        $str = str_replace("+", "", $str);
        $str = str_replace("“", "", $str);
        $str = str_replace("|", "", $str);
        $str = str_replace("!", "", $str);
        $str = str_replace("–", "-", $str);
        $str = str_replace("---", "-", $str);
        $str = str_replace("--", "-", $str);
        return $str;
    }
    /**
     * Kiểm tra kí tự bắt đầu chuỗi
     * @param string $haystack Chuỗi cần kiểm tra
     * @param string $needle Chuỗi kiểm tra
     * @param boolean $case Có phân biệt chữ hoa thường hay không ?
     */
    public static function startsWith ($haystack, $needle, $case = true)
    {
        if ($case)
            return (strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
        return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
    }
    /**
     * Kiểm tra kí tự kết thúc chuỗi 
     * @param string $haystack Chuỗi cần kiểm tra
     * @param string $needle Chuỗi kiểm tra
     * @param boolean $case Có phân biệt chữ hoa thường hay không ?
     */
    public static function endsWith ($haystack, $needle, $case = true)
    {
        if ($case)
            return (strcmp(
            substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
        return (strcasecmp(
        substr($haystack, strlen($haystack) - strlen($needle)), $needle) === 0);
    }
    /**
     * Xóa các đoạn mã độc tấn công XSS
     * @author: Daniel Morris
     * @copyright: Daniel Morris
     * @license: GNU General Public License (GPL)
     * @param $source string Chuỗi cần clean
     */
    public static function xssClean ($source)
    {
        $tagsMethod = 0;
        $tagsArray = array();
        $tagBlacklist = array('applet', 'body', 'bgsound', 'base', 'basefont', 
        'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 
        'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 
        'xml');
        $preTag = NULL;
        $postTag = $source;
        $tagOpen_start = strpos($source, '<');
        while ($tagOpen_start !== FALSE) {
            $preTag .= substr($postTag, 0, $tagOpen_start);
            $postTag = substr($postTag, $tagOpen_start);
            $fromTagOpen = substr($postTag, 1);
            $tagOpen_end = strpos($fromTagOpen, '>');
            if ($tagOpen_end === false)
                break;
            $tagOpen_nested = strpos($fromTagOpen, '<');
            if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
                $preTag .= substr($postTag, 0, ($tagOpen_nested + 1));
                $postTag = substr($postTag, ($tagOpen_nested + 1));
                $tagOpen_start = strpos($postTag, '<');
                continue;
            }
            $tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
            $currentTag = substr($fromTagOpen, 0, $tagOpen_end);
            $tagLength = strlen($currentTag);
            if (! $tagOpen_end) {
                $preTag .= $postTag;
                $tagOpen_start = strpos($postTag, '<');
            }
            $tagLeft = $currentTag;
            $attrSet = array();
            $currentSpace = strpos($tagLeft, ' ');
            if (substr($currentTag, 0, 1) == "/") {
                $isCloseTag = TRUE;
                list ($tagName) = explode(' ', $currentTag);
                $tagName = substr($tagName, 1);
            } else {
                $isCloseTag = FALSE;
                list ($tagName) = explode(' ', $currentTag);
            }
            if ((! preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (! $tagName) ||
             ((in_array(strtolower($tagName), $tagBlacklist)))) {
                $postTag = substr($postTag, ($tagLength + 2));
                $tagOpen_start = strpos($postTag, '<');
                continue;
            }
            while ($currentSpace !== FALSE) {
                $fromSpace = substr($tagLeft, ($currentSpace + 1));
                $nextSpace = strpos($fromSpace, ' ');
                $openQuotes = strpos($fromSpace, '"');
                $closeQuotes = strpos(substr($fromSpace, ($openQuotes + 1)), 
                '"') + $openQuotes + 1;
                if (strpos($fromSpace, '=') !== FALSE) {
                    if (($openQuotes !== FALSE) && (strpos(
                    substr($fromSpace, ($openQuotes + 1)), '"') !== FALSE))
                        $attr = substr($fromSpace, 0, ($closeQuotes + 1));
                    else
                        $attr = substr($fromSpace, 0, $nextSpace);
                } else
                    $attr = substr($fromSpace, 0, $nextSpace);
                if (! $attr)
                    $attr = $fromSpace;
                $attrSet[] = $attr;
                $tagLeft = substr($fromSpace, strlen($attr));
                $currentSpace = strpos($tagLeft, ' ');
            }
            $tagFound = in_array(strtolower($tagName), $tagsArray);
            if ((! $tagFound && $tagsMethod) || ($tagFound && ! $tagsMethod)) {
                if (! $isCloseTag) {
                    $attrSet = self::_filterAttr($attrSet);
                    $preTag .= '<' . $tagName;
                    for ($i = 0; $i < count($attrSet); $i ++)
                        $preTag .= ' ' . $attrSet[$i];
                    if (strpos($fromTagOpen, "</" . $tagName))
                        $preTag .= '>';
                    else
                        $preTag .= ' />';
                } else
                    $preTag .= '</' . $tagName . '>';
            }
            $postTag = substr($postTag, ($tagLength + 2));
            $tagOpen_start = strpos($postTag, '<');
        }
        $preTag .= $postTag;
        return $preTag;
    }
    
    //hopvq cắt chuỗi
    public static function get_num_word($str, $num) //$num so ky tu can cat
    {
		if (strlen($str) > $num){
			$str = substr($str, 0, $num);
			$pos = strrpos($str, " ");
			if($pos === false) {
				return substr($str, 0, $num)."...";
			}
			return substr($str, 0, $pos)."...";
		}else{
			return $str;
		}
    }
    
    function removeMark($str){
    	$str = str_replace(array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ"), "a", $str);
    	$str = str_replace(array("À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ"), "a", $str);
    
    	$str = str_replace(array("è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ"),"e", $str);
    	$str = str_replace(array("È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ"),"e", $str);
    
    	$str = str_replace(array("ì","í","ị","ỉ","ĩ"),"i", $str);
    	$str = str_replace(array("Ì","Í","Ị","Ỉ","Ĩ"),"i", $str);
    
    	$str = str_replace(array("ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ"), "o", $str);
    	$str = str_replace(array("Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ"), "o", $str);
    
    	$str = str_replace(array("ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ"), "u", $str);
    	$str = str_replace(array("Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ"), "u", $str);
    
    	$str = str_replace(array("ỳ","ý","ỵ","ỷ","ỹ"), "y", $str);
    	$str = str_replace(array("Ỳ","Ý","Ỵ","Ỷ","Ỹ"), "y", $str);
    
    	$str = str_replace(array("đ", "Đ"),"d", $str);
    	$str = str_replace(array("!","@","%","^","*","(",")","+","=","<",">","?","/",",",".",":",";","'"," ","\"","&","#","[","]","~","$","_"),"-", $str);
    	$str = str_replace(array("--"),"-", $str); //thay thế 2- thành 1-
    	return strtolower($str);
    }// end of removeMark
    
    /**
     * Xóa các đoạn mã tấn công xss trong attribute
     * @author: Daniel Morris
     * @copyright: Daniel Morris
     * @license: GNU General Public License (GPL)
     * @param array $attrSet
     */
    private static function _filterAttr ($attrSet)
    {
        $attrArray = array();
        $attrMethod = 0;
        $attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 
        'lowsrc');
        $newSet = array();
        for ($i = 0; $i < count($attrSet); $i ++) {
            if (! $attrSet[$i])
                continue;
            $attrSubSet = explode('=', trim($attrSet[$i]));
            list ($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
            if ((! eregi("^[a-z]*$", $attrSubSet[0])) || (((in_array(
            strtolower($attrSubSet[0]), $attrBlacklist)) ||
             (substr($attrSubSet[0], 0, 2) == 'on'))))
                continue;
            if ($attrSubSet[1]) {
                $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
                $attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
                $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
                if ((substr($attrSubSet[1], 0, 1) == "'") &&
                 (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
                    $attrSubSet[1] = substr($attrSubSet[1], 1, 
                    (strlen($attrSubSet[1]) - 2));
                $attrSubSet[1] = stripslashes($attrSubSet[1]);
            }
            if (((strpos(strtolower($attrSubSet[1]), 'expression') !== false) &&
             (strtolower($attrSubSet[0]) == 'style')) ||
             (strpos(strtolower($attrSubSet[1]), 'javascript:') !== false) ||
             (strpos(strtolower($attrSubSet[1]), 'behaviour:') !== false) ||
             (strpos(strtolower($attrSubSet[1]), 'vbscript:') !== false) ||
             (strpos(strtolower($attrSubSet[1]), 'mocha:') !== false) ||
             (strpos(strtolower($attrSubSet[1]), 'livescript:') !== false))
                continue;
            $attrFound = in_array(strtolower($attrSubSet[0]), $attrArray);
            if ((! $attrFound && $attrMethod) || ($attrFound && ! $attrMethod)) {
                if ($attrSubSet[1])
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
                else 
                    if ($attrSubSet[1] == "0")
                        $newSet[] = $attrSubSet[0] . '="0"';
                    else
                        $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
            }
        }
        return $newSet;
    }

    /**
     * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
     * @param mixed   $in     String or long input to translate
     * @param boolean $to_num  Reverses translation when true
     * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
     * @param string  $passKey Supplying a password makes it harder to calculate the original ID
     *
     * @return mixed string or long
     */
    public static function alphaID($in, $to_num = false, $pad_up = false, $passKey = null)
    {
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($passKey !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for ($n = 0; $n<strlen($index); $n++) {
                $i[] = substr( $index,$n ,1);
            }

            $passhash = hash('sha256',$passKey);
            $passhash = (strlen($passhash) < strlen($index))
                ? hash('sha512',$passKey)
                : $passhash;

            for ($n=0; $n < strlen($index); $n++) {
                $p[] =  substr($passhash, $n ,1);
            }

            array_multisort($p,  SORT_DESC, $i);
            $index = implode($i);
        }

        $base  = strlen($index);

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $in  = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number  -->>  alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a   = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in  = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;
    }
}