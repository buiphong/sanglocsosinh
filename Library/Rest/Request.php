<?php
/**
 * Rest_Request Request của một Client gửi tới Service
 *
 * @author		http://www.gen-x-design.com/archives/create-a-rest-api-with-php/
 * @author		https://github.com/philsturgeon/codeigniter-restserver
 */
class Rest_Request
{
    /**
     * Danh sách tất cả các phương thức hỗ trợ và định dạng dữ liệu
     * @var array
     */
    private $_supported_formats = array('xml' => 'application/xml', 
    'rawxml' => 'application/xml', 'json' => 'application/json', 
    'jsonp' => 'application/javascript', 
    'serialize' => 'application/vnd.php.serialized', 'php' => 'text/plain', 
    'html' => 'text/html', 'csv' => 'application/csv');
    private $request_vars;
    private $data;
    private $http_accept;
    private $method;
    private $format;
    /**
     * Khởi tạo một Request
     */
    public function __construct ()
    {
        $this->request_vars = array();
        $this->data = '';
        $this->http_accept = $_SERVER['HTTP_ACCEPT'];
        $this->method = 'get';
        $this->format = $this->_detectFormat();
    }
    /**
     * Thiết lập tham số
     * @param mixed $data
     */
    public function setData ($data)
    {
        $this->data = $data;
    }
    /**
     * Thiết lập phương thức gửi từ Client
     * @param string $method
     */
    public function setMethod ($method)
    {
        $this->method = $method;
    }
    /**
     * Thiết lập các biến request
     * @param mixed $request_vars
     */
    public function setRequestVars ($request_vars)
    {
        $this->request_vars = $request_vars;
    }
    /**
     * Lấy ra tham số
     */
    public function getData ()
    {
        return $this->data;
    }
    /**
     * Lấy ra phương thức gửi từ Client
     */
    public function getMethod ()
    {
        return $this->method;
    }
    /**
     * Lấy ra HttpAccept
     */
    public function getHttpAccept ()
    {
        return $this->http_accept;
    }
    /**
     * Lấy ra các biến Request
     */
    public function getRequestVars ()
    {
        return $this->request_vars;
    }
    /**
     * Lấy ra định dạng dữ liệu cần trả về
     */
    public function getFormat ()
    {
        return $this->format;
    }
    /**
     * Lấy ra content-type của dữ liệu trả về
     * @param string $format
     */
    public function getFormatContentType ($format = NULL)
    {
        if ($format !== NULL)
            return $this->_supported_formats[$format];
        else
            return $this->_supported_formats[$this->format];
    }
    /**
     * Tự động nhận biết loại định dạng dữ liệu cần trả về
     */
    public function _detectFormat ()
    {
        $pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) .
         ')$/';
        // A format has been passed as an argument in the URL and it is supported
        if (isset($_REQUEST['format']) &&
         isset($this->_supported_formats)) {
            return $_REQUEST['format'];
        }
        // Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
        if ($this->getHttpAccept()) {
            // Check all formats against the HTTP_ACCEPT header
            foreach (array_keys($this->_supported_formats) as $format) {
                // Has this format been requested?
                if (strpos($this->getHttpAccept(), $format) !==
                 FALSE) {
                    // If not HTML or XML assume its right and send it on its way
                    if ($format != 'html' && $format != 'xml') {
                        return $format;
                    } // HTML or XML have shown up as a match
else {
                        // If it is truely HTML, it wont want any XML
                        if ($format == 'html' &&
                         strpos($this->getHttpAccept(), 'xml') === FALSE) {
                            return $format;
                        } // If it is truely XML, it wont want any HTML
elseif ($format == 'xml' && strpos(
                        $this->getHttpAccept(), 'html') === FALSE) {
                            return $format;
                        }
                    }
                }
            }
        } // Kết thúc kiểm tra HTTP_ACCEPT
        //Mặc định sử dụng json
        return 'json';
    }
    /**
     * Lấy ra danh sách các loại dữ liệu được hỗ trợ từ Service
     */
    public function getSupportedFormats ()
    {
        return $this->_supported_formats;
    }
}