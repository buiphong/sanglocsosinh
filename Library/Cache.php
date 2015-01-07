<?php
class Cache
{
	public static function save($key, $value)
    {
        $cacheDir = ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR . DIRECTORY_SEPARATOR;
        if(file_put_contents($cacheDir . md5($key) . '.cache', $value))
            return true;
        else
            return false;
    }

    public static function load($key)
    {
        $cacheDir = ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR . DIRECTORY_SEPARATOR;
        if(file_exists($cacheDir . md5($key) . '.cache'))
        {
            //return file content
            return file_get_contents($cacheDir . md5($key) . '.cache');
        }
        else
            return false;
    }
}