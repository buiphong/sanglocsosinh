<?php
/**
 * Hỗ trợ phân tích comment class lấy attribute
 */
class Attribute_Parser
{
	const SPECIAL_CHAR = - 1;

	/**
	 * @param string $path Đường dẫn đầy đủ đến tệp tin mã nguồn PHP
	 * @return string Mã nguồn PHP để xây dựng các Attribute
	 */
	public function parse ($path)
	{
		return $this->parseSource(file_get_contents($path), $path);
	}
	/**
	 * @param string $source Mã nguồn PHP cần phân tích
	 * @param string $path Đường dẫn tới tệp tin nguồn cần phân tích cú pháp
	 * @return string Mã nguồn PHP để khởi tạo Attributes
	 */
	protected function parseSource ($source, $path)
	{
		//Mảng lưu trữ tất cả attributes của các thành phần trong file PHP
		$attributes = array();
		//Tên lớp
		$class = "";
		//Đánh dấu bắt đầu member
		$member = false;
		//Mảng chứa các attribute khi phân tích các thành phần
		$memberAttributes = array();
		//Phân tích file
		foreach (token_get_all($source) as $token) {
			list ($type, $str, $line) = is_array($token) ? $token : array(self::SPECIAL_CHAR, $token, $line);
			switch ($type) {
				case T_DOC_COMMENT:
				case T_COMMENT:
					$attr = $this->parseComment($str);
					if (! empty($attr))
						$memberAttributes = array_merge($memberAttributes,$attr);
					break;
				case T_CLASS:
					$member = T_CLASS;
					break;
				case T_VARIABLE:
					if (! empty($memberAttributes)) {
						$attributes[$class . "::" . $str] = $memberAttributes;
						$memberAttributes = array();
					}
					break;
				case T_FUNCTION:
					$member = T_FUNCTION;
					break;
				case T_STRING:
					if ($member) {
						if ($member == T_CLASS) {
							$class = $str;
							//Nếu có attribute thì đưa vào mảng chứa cùng với tên lớp và gán mảng attribute rỗng
							if (! empty($memberAttributes)) {
								$attributes[$str] = $memberAttributes;
								$memberAttributes = array();
							}
						} else
							if ($member == T_FUNCTION) {
							if (! empty($memberAttributes)) {
								$attributes[$class . "::" . $str . '()'] = $memberAttributes;
								$memberAttributes = array();
							}
						}
						$member = false;
					}
					break;
					//Bỏ qua
				case T_WHITESPACE:
				case T_PUBLIC:
				case T_PROTECTED:
				case T_PRIVATE:
				case T_ABSTRACT:
				case T_FINAL:
				case T_VAR:
				case T_REQUIRE:
				case T_REQUIRE_ONCE:
				case T_INCLUDE:
				case T_INCLUDE_ONCE:
				case self::SPECIAL_CHAR:
				case T_CONSTANT_ENCAPSED_STRING:
					break;
				default:
					$memberAttributes = array();
					break;
			}
		}
		return $attributes;
	}

	/**
	 * Phân tích comment để lấy ra Attribute
	 * @param string $str Mã chú thích trong tệp tin PHP được lọc bằng parseSource()
	 * @return array Đoạn mã nguồn PHP với mảng các khởi tạo Attribute
	 */
	protected function parseComment ($str)
	{
		//Mảng chứa các Attribute Object
		$attributes = array();
		//Xóa các kí tự comment
		$str = trim(preg_replace('/^[\/\*\# \t]+/m', '', $str));
		$str = str_replace("\r\n", "\n", $str);
		//Lấy ra các chuỗi nằm trong dấu []
		preg_match_all('/\[(.+)\]/', $str, $matches);
		//Xóa các phần tử trống trong $matches
		$matches = PTArray::trim($matches);
		//Nếu lấy ra mảng có chứa 2 phần tử thì mảng này hợp lệ để xử lý
		if (! empty($matches) && count($matches) == 2) {
			foreach ($matches[1] as $s) {
				//Khởi tạo các giá trị
				//Tên Attribute
				$attrName = false;
				//Mảng các tham số truyền vào Attribute
				$attrArgs = array();
				//Nếu gọi attribute có () truyền vào tham số
				if (stripos($s, "(")) {
					preg_match('/\((.+)\)/', $s, $params);
					preg_match('/^(\w+)\(/', $s, $attrNs);
					//Nếu mảng chứa tên của attribute valid
					if (count($attrNs) == 2) {
						//Tên thuộc tính
						$attrName = $attrNs[1];
						//Nếu có tham số truyền vào
						if (count($params) == 2) {
							$arguments = explode(",", $params[1]);
							foreach ($arguments as $argument)
							{
								$argument = trim($argument);
								$args = explode("=", $argument);
								//Nếu không truyền vào tên thuộc tính
								if (count($args) ==1)
									@eval('$attrArgs[] = ' . trim($args[0]) . ';');
								else
									if (count($args) == 2)
									{
										//Truyền vào tên thuộc tính và giá trị
										//Lấy ra tên và xóa các kí tự ' hoặc "
										$name = trim($args[0]);
										//Nếu tên được bọc bởi các dấu ' hoặc dấu "
										if (substr($name, 0, 1) == "'" || substr($name, 0, 1) == '"')
											eval('$attrArgs[' . $name . '] = ' . trim($args[1]) . " ;");
										else
											eval('$attrArgs["' . $name . '"] = ' .trim($args[1]) . " ;");
									}
							}
						}
					}
				} else { //Nếu gọi attribute không có () và không có tham số truyền vào
					$attrName = trim($s);
				}
				$attributes[$attrName] = $attrArgs;
			}
		}
		return $attributes;
	}
}