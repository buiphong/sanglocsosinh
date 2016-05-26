<?php
/**
 * Lớp hỗ trợ xử lý XML
 *
 * @author		buiphong
 */
class Xml
{
    /**
     * DOMDocument
     * @var DOMDocument
     */
    public $dom;
    public function __construct ($xml)
    {
        $this->dom = new DOMDocument();
        if (is_file($xml))
            $this->dom->load($xml);
        else 
            if (is_string($xml))
                $this->dom->loadXML($xml);
        return false;
    }
    /**
     * XMLNode
     * @param DOMNode $node
     */
    private function _processToArray ($node)
    {
        $result = array();
        $occurance = array();
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                if (isset($occurance[$child->nodeName]))
                    $occurance[$child->nodeName] ++;
                else
                    $occurance[$child->nodeName] = 1;
            }
        }
        if ($node->nodeType == XML_TEXT_NODE) {
            $result = html_entity_decode(
            htmlentities($node->nodeValue, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 
            'UTF-8');
        } else {
            if ($node->hasChildNodes()) {
                $children = $node->childNodes;
                for ($i = 0; $i < $children->length; $i ++) {
                    $child = $children->item($i);
                    if ($child->nodeName != '#text') {
                        if ($occurance[$child->nodeName] > 1) {
                            $result[$child->nodeName][] = $this->_processToArray(
                            $child);
                        } else {
                            $result[$child->nodeName] = $this->_processToArray(
                            $child);
                        }
                    } else 
                        if ($child->nodeName == '#text') {
                            $text = $this->_processToArray($child);
                            if (trim($text) != '') {
                                $result[$child->nodeName] = $this->_processToArray(
                                $child);
                            }
                        }
                }
            }
            if ($node->hasAttributes()) {
                $attributes = $node->attributes;
                if (! is_null($attributes)) {
                    foreach ($attributes as $key => $attr) {
                        $result["@" . $attr->name] = $attr->value;
                    }
                }
            }
        }
        return $result;
    }
    /**
     * Lấy ra mảng từ xml
     */
    function getArray ()
    {
        return $this->_processToArray($this->dom->documentElement);
    }
    /**
     * Phương thức chuyển file xml thành mảng.
     * @param string $xmlUrl
     */
    public static function toArray ($xmlUrl, $arrSkipIndices = array())
    {
        $arrData = array();
        //Nếu dữ liệu truyền vào là kiểu chuỗi
        if (is_string($xmlUrl)) {
            $xmlStr = file_get_contents($xmlUrl);
            $xmlUrl = simplexml_load_string($xmlStr, null, LIBXML_NOCDATA);
        }
        //Neu du lieu nhap vao la object
        if (is_object($xmlUrl)) {
            $xmlUrl = get_object_vars($xmlUrl);
        }
        if (is_array($xmlUrl)) {
            foreach ($xmlUrl as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = PTArray::xmlToArray($value); // Gọi đệ quy
                }
                if (in_array($index, $arrSkipIndices)) {
                    continue;
                }
                $arrData[$index] = $value;
            }
        }
        return $arrData;
    }
}