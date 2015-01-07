<?php
/**
 * Class Rest Controller
 * @author buiphong
 */
class RestController extends Controller_Abstract
{
	/**
	 * Đối tượng hỗ trợ url
	 */
	public $url;
	
	/**
	 * Đối tượng tương tác với cơ sở dữ liệu
	 */
	public $db;
	
	/**
	 * Rest request
	 */
	protected $request;
	
	/**
	 * Khởi tạo controller
	 * @param Router $router || null
	 */
	public function __construct()
	{
		$this->request = Rest_Utils::processRequest();
        if($this->request->getData())
		    $this->params = array_merge($this->params,(array) $this->request->getData());
	}
	
	public function reAnalysisRequest()
	{
		$this->params = array();
	}
	
	/**
	 * Gán include path
	 */
	public function setIncludePath()
	{
		//Lấy ra đường dẫn module sử dụng
		if(!empty($this->router->module))
			//$folder = $this->router->getModulePath($this->router->module);
			$folder = $this->router->module;
		else
			$folder = APPLICATION_PATH;
		//Set đường dẫn
		set_include_path(get_include_path() . PATH_SEPARATOR . 'modules' . PATH_SEPARATOR . $folder);
		//		. PATH_SEPARATOR . $folder . '/Controllers');
	}
	
	public function isAjaxRequest()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return true;
		else
			return false;
	}
	
	public function response ($data = array(), $http_code = 200)
	{
		// If the format method exists, call and return the output in that format
		if (method_exists($this, '_format_' . $this->request->getFormat())) {
				// Set the correct format header
			$contentType = $this->request->getFormatContentType();
			$formatted_data = $this->{'_format_' . $this->request->getFormat()}($data);
			$output = $formatted_data;
		} else {
			// Format not supported, output directly
			$output = $data;
		}
		Rest_Utils::sendResponse($http_code, $output, $contentType);
	}
	// Force it into an array
	private function _force_loopable ($data)
	{
		// Force it to be something useful
		if (! is_array($data) and ! is_object($data)) {
			$data = (array) $data;
		}
		return $data;
	}
	// FORMATING FUNCTIONS ---------------------------------------------------------
	// Format XML for output
	private function _format_xml ($data = array(), $structure = NULL, $basenode = 'xml')
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1) {
			ini_set('zend.ze1_compatibility_mode', 0);
		}
		if ($structure == NULL) {
			$structure = simplexml_load_string(
					"<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}
		// loop through the data passed in.
		$data = $this->_force_loopable($data);
		foreach ($data as $key => $value) {
			// no numeric keys in our xml please!
			if (is_numeric($key)) {
				// make string key...
				//$key = "item_". (string) $key;
				$key = "item";
			}
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_]/i', '', $key);
			// if there is another array found recrusively call this function
			if (is_array($value) || is_object($value)) {
				$node = $structure->addChild($key);
				// recrusive call.
				$this->_format_xml($value, $node, $basenode);
			} else {
				// Actual boolean values need to be converted to numbers
				is_bool($value) and $value = (int) $value;
				// add single node.
				$value = htmlspecialchars(
						html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES,
						"UTF-8");
				$UsedKeys[] = $key;
				$structure->addChild($key, $value);
			}
		}
		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}
	
	// Format Raw XML for output
	private function _format_rawxml ($data = array(), $structure = NULL, $basenode = 'xml')
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1) {
			ini_set('zend.ze1_compatibility_mode', 0);
		}
		if ($structure == NULL) {
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}
		// loop through the data passed in.
		$data = $this->_force_loopable($data);
		foreach ($data as $key => $value) {
			// no numeric keys in our xml please!
			if (is_numeric($key)) {
				// make string key...
				//$key = "item_". (string) $key;
				$key = "item";
			}
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z0-9_-]/i', '', $key);
			// if there is another array found recrusively call this function
			if (is_array($value) || is_object($value)) {
				$node = $structure->addChild($key);
				// recrusive call.
				$this->_format_rawxml($value, $node, $basenode);
			} else {
				// Actual boolean values need to be converted to numbers
				is_bool($value) and $value = (int) $value;
				// add single node.
				$value = htmlspecialchars(
						html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES,
						"UTF-8");
				$UsedKeys[] = $key;
				$structure->addChild($key, $value);
			}
		}
		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}
	
	// Format HTML for output
	private function _format_html ($data = array())
	{
		return "DOES NOT SUPPORT IN CURRENT VERSION";
	}
	// Format HTML for output
	private function _format_csv ($data = array())
	{
		// Multi-dimentional array
		if (isset($data[0])) {
			$headings = array_keys($data[0]);
		} // Single array
		else {
			$headings = array_keys($data);
			$data = array($data);
		}
		$output = implode(',', $headings) . "\r\n";
		foreach ($data as &$row) {
			$output .= '"' . implode('","', $row) . "\"\r\n";
		}
		return $output;
	}
	// Encode as JSON
	private function _format_json ($data = array())
	{
		return json_encode($data);
	}
	// Encode as JSONP
	private function _format_jsonp ($data = array())
	{
		return $this->params['callback'] . '(' . json_encode($data) . ')';
	}
	// Encode as Serialized array
	private function _format_serialize ($data = array())
	{
		return serialize($data);
	}
	// Encode raw PHP
	private function _format_php ($data = array())
	{
		return var_export($data, TRUE);
	}
}
