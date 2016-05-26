<?php
require_once 'Xml.php';
/**
 * Lớp hỗ trợ các vấn đề liên quan đến mảng
 * @author		Mr.UBKey
 */
class PTArray
{
    /**
     * Phương thức chuyển mảng thành đối tượng
     * @param array $arr
     */
    public static function toObject ($arr)
    {
        $o = new stdClass();
        foreach ($arr as $key => $value) {
            if (! is_array($value))
                $o->$key = $value;
            else {
                $o->$key = self::toObject($value);
            }
        }
        return $o;
    }
    /**
     * Phương thức chuyển file xml thành mảng.
     * @param string $xmlUrl
     */
    public static function xmlToArray ($xmlUrl, $arrSkipIndices = array())
    {
        return Xml::toArray($xmlUrl, $arrSkipIndices);
    }
    /**
     * Loại bỏ các kí tự đặc biệt để bảo mật cho mảng
     * @param array $array
     */
    public static function secure ($array)
    {
        require_once 'XPHP/String.php';
        for ($i = 0; $i < sizeof($array); $i ++) {
            if (is_string($array[$i]))
                $array[$i] = String::secure($array[$i]);
            else 
                if (is_array($array[$i]))
                    $array[$i] = self::secure($array[$i]);
        }
        return $array;
    }
    /**
     * Chuyển một mảng thành dữ liệu xml
     *
     * @param array $data
     * @param string $rootNodeName - Tên của rootnode mặc định là data.
     * @param SimpleXMLElement $xml - nên được sử dụng đệ quy
     * @return string XML
     */
    public static function toXml ($data, $rootNodeName = 'data', $xml = null)
    {
        // Tắt các chế độ tương thích
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }
        if ($xml == null) {
            $xml = simplexml_load_string(
            "<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }
        //Lặp dữ liệu
        foreach ($data as $key => $value) {
            //Không đặt key là số nếu key là số sẽ là unknownNode_số
            if (is_numeric($key)) {
                // make string key...
                $key = "unknownNode_" . (string) $key;
            }
            //Xử lý các kí tự đặc biệt
            $key = preg_replace('/[^a-z]/i', '', $key);
            //Nếu tìm thấy mảng gọi hồi quy
            if (is_array($value)) {
                $node = $xml->addChild($key);
                //gọi hồi quy.
                self::toXml($value, $rootNodeName, $node);
            } else {
                //Thêm một nút mới
                $value = htmlentities($value);
                $xml->addChild($key, $value);
            }
        }
        //Trả về một chuỗi xml
        return $xml->asXML();
    }
    /**
     * Trim khoảng trắng tất cả các phần tử trong mảng
     * @param array $array
     */
    public static function trim ($array)
    {
        foreach ($array as $key => $ele)
            if (empty($ele))
                unset($array[$key]);
        return $array;
    }
}
