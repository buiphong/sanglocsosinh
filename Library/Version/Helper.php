<?php
/**
 * Lớp hỗ trợ kiểm tra phiên bản, update module
 */
class Version_Helper
{
    /**
     * @param $module
     * @return stdClass
     */
    public static function getLatest($module)
    {
        //Get version service module
        $serviceUrl = Version_Helper::getVersionConfig($module . ':version_service_url');
        if($serviceUrl)
        {
            $curl = curl_init($serviceUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
            if($result)
                return json_decode($result);
        }
        return false;
    }

    /**
     * Get current version of module
     */
    public static function getVersion($module)
    {
        $result = Version_Helper::getVersionConfig($module . ':version');
        if($result)
            return $result;
        else
            return 'Unknown';
    }

    /**
     * Get current database version of module
     */
    public static function getDBVersion($module)
    {
        $result = Version_Helper::getVersionConfig($module . ':db_version');
        if($result)
            return $result;
        else
            return 'Unknown';
    }

    /**
     * Check if file version is exist
     */
    public function existVersionFile($module)
    {
        if(file_exists(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .'Config'.DIRECTORY_SEPARATOR.'Version.php'))
            return true;
        return false;
    }

    /**
     * Check if file update is exist
     */
    public function existUpdateFile($module)
    {
        if(file_exists(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .'Config'.DIRECTORY_SEPARATOR.'Update.php'))
            return true;
        return false;
    }

    /**
     * Get version config
     */
    public static function getVersionConfig($key)
    {
        if($key && strpos($key, ':') !== false)
        {
            //Get config module
            $a = explode(':', $key);
            if(Version_Helper::existVersionFile($a[0]))
            {
                require ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR.'Modules' . DIRECTORY_SEPARATOR . $a[0] .
                    DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Version.php';
                if(!isset(${$a[1]}))
                    throw new Exception('Không tìm thấy thông tin cấu hình `'.$a[1].'` tại: ' . 'Application'. DIRECTORY_SEPARATOR.'Modules' .
                        DIRECTORY_SEPARATOR . $a[0] .DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'Version.php');
                else
                    return ${$a[1]};
            }
        }
        return false;
    }

    /**
     * Get update config
     */
    public static function getUpdateConfig($key)
    {
        if($key && strpos($key, ':') !== false)
        {
            //Get config module
            $a = explode(':', $key);
            if(Version_Helper::existUpdateFile($a[0]))
            {
                require ROOT_PATH . DIRECTORY_SEPARATOR . 'Application'.DIRECTORY_SEPARATOR.'Modules' . DIRECTORY_SEPARATOR . $a[0] .
                    DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Update.php';
                if(!isset(${$a[1]}))
                    throw new Exception('Không tìm thấy thông tin cấu hình `'.$a[1].'` tại: ' . 'Application'. DIRECTORY_SEPARATOR.'Modules' .
                        DIRECTORY_SEPARATOR . $a[0] .DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'Update.php');
                else
                    return ${$a[1]};
            }
        }
        return false;
    }
}