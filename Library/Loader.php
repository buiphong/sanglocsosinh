<?php
/**
 * Lớp Loader của hệ thống, được sử dụng để loadFile, 
 * loadClass hoặc autoload các lớp
 *
 * @package		Loader
 * @author		buiphong
 * @link		http://xphp.xweb.vn/user_guide/xphp_loader.html
 */
class Loader
{
    /**
     * Loader
     */
    public static $loader;
    /**
     * Đăng ký tự động load các lớp
     */
    public static function registerAutoload ()
    {
        if (self::$loader == NULL)
            self::$loader = new self();
        return self::$loader;
    }
    /**
     * Khởi tạo lớp Loader
     */
    public function __construct ()
    {
        spl_autoload_register(array($this, '_autoload'));
    }
    /**
     * Phương thức đặt trong autoload
     * @param string $class
     */
    protected function _autoload ($class)
    {
        self::loadClass($class);
    }
    /**
     * Load một lớp từ file PHP. Tên file phải được định dạng theo chuẩn Zend
     *
     * @param string $class Tên lớp đầy đủ.
     * @param string|array $dirs Tùy chọn đường dẫn hoặc mảng các đường dẫn để tìm kiếm.
     * @return void
     */
    public static function loadClass ($class, $dirs = null)
    {
        // Tự động nhận đường dẫn từ tên lớp
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        self::loadFile($file, null, true);
    }
    /**
     * Load tệp tin PHP vào hệ thống
     * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd     New BSD License 
     * @param  string        $filename Tên file muốn load
     * @param  string|array  $dirs Đường dẫn tới thư mục chứa file hoặc mảng các đường dẫn thư mục chứa file để tìm kiếm.
     * @param  boolean       $once $once = true load file sẽ sử dụng hàm include_once() và ngược lại
     * @return boolean
     */
    public static function loadFile ($filename, $dirs = null, $once = false)
    {

        self::_securityCheck($filename);
        /**
         * Tìm kiếm file trong các dường dẫn $dirs và đường dẫn include
         */
        $incPath = false;
        if (! empty($dirs) && (is_array($dirs) || is_string($dirs))) {
            if (is_array($dirs)) {
                $dirs = implode(PATH_SEPARATOR, $dirs);
            }
            $incPath = get_include_path();
            set_include_path($dirs . PATH_SEPARATOR . $incPath);
        }
        /**
         * Tìm kiếm tên tệp tin trong include_path
         */
        if ($once) {
            include_once $filename;
        } else {
            include $filename;
        }
        /**
         * Nếu tìm thấy tệp tin trong thư mục. Thiết lập đường dẫn tới thư mục vào include_path
         */
        if ($incPath) {
            set_include_path($incPath);
        }
        return true;
    }
    /**
     * Tách các đường đãn include thành mảng và trả về mảng này
     * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd     New BSD License
     * @param  string|null $path 
     * @return array
     */
    public static function explodeIncludePath ($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }
        if (PATH_SEPARATOR == ':') {
            //Unix system
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }
    /**
     * Kiểm tra các kí tự bảo mật chi tên tệp tin
     * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd     New BSD License
     * @param  string $filename Tên file
     * @return void
     */
    protected static function _securityCheck ($filename)
    {
        /**
         * Security check
         */
        if (preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $filename)) {
            throw new Exception(
            'Bảo mật: Tìm thấy các kí tự đặc biệt trong tên tệp tin');
        }
    }
}
