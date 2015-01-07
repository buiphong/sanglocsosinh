<?php
/**
 * Application config
 */
class Config
{
    /**
     * Lấy cấu hình ứng dụng
     * $key: Có thể là tên cấu hình 'name', hoặc là cấu hình của module: 'Module:configName'
     */
    public static function getConfig($key)
    {
        if($key && strpos($key, ':') !== false)
        {
            //Get config module
            $a = explode(':', $key);
            $path = Router::getModulePath($a[0]);
            require $path . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Config.php';
            if(!isset(${$a[1]}))
                throw new Exception('Không tìm thấy thông tin cấu hình `'.$a[1].'` tại: ' . 'Application'.
                    DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'Config.php');
            else
                return ${$a[1]};
        }
        elseif($key)
        {
            require ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR.'Config'.
                DIRECTORY_SEPARATOR.'Config.php';
            //Get config module
            if(!isset(${$key}))
                throw new Exception('Không tìm thấy thông tin cấu hình `'.$key.'` tại: ' . 'Application'.
                    DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'Config.php');
            else
                return ${$key};
        }
        return false;
    }
}