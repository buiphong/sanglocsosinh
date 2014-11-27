<?php
//$time = microtime(true);
error_reporting(E_ALL);
ini_set('display_errors','on');
session_start();
date_default_timezone_set("Asia/Saigon");
//Set header charset
header("Content-Type: text/html; charset=UTF-8");
header("Connection: Keep-alive");
//Gzip compresses
ini_set('zlib_output_compression', 'On');
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
    ob_start("ob_gzhandler");
else
    ob_start();

// Define đường dẫn tới thư mục modules
defined('ROOT_PATH') || define('ROOT_PATH', realpath(dirname(dirname(__FILE__))));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_PATH . DIRECTORY_SEPARATOR .'Application');
set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
//required element
require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'Library/Loader.php';
Loader::registerAutoload();
//Load setting
require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'settings.php';

if( defined('COMBINE_ASSETS_DIR') && !is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . COMBINE_ASSETS_DIR))
    VccDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . COMBINE_ASSETS_DIR);
if( defined('CACHE_FRONTEND_DIR') && !is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR))
    VccDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR);
if( defined('SEARCH_DIR') && !is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . SEARCH_DIR))
    VccDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . SEARCH_DIR);
if( defined('THUMBNAIL_DIR') && !is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . THUMBNAIL_DIR))
    VccDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . THUMBNAIL_DIR);
if( defined('RUNTIME_DIR') && !is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . RUNTIME_DIR))
    VccDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . RUNTIME_DIR);

//Load language
PTLanguage::loadResource();
//load default router
if(isset($defaultRouter));
    PTRegistry::set('defaultRouter', $defaultRouter);

$Controller = new Controller_Front();
echo $Controller->dispatch();

//Close mongodb connection
if (PTRegistry::isRegistered('Db_mongodb')) {
	$mongoConn = PTRegistry::get('Db_mongodb');
	if (!empty($mongoConn)) {
		$mongoConn->connection->close();
	}
}
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
    ob_end_flush();
else
    ob_end_flush();

/*$extime = microtime(true) - $time;
echo "Thời gian : " . ($extime * 10) . " (ms)";
echo "<br />";
echo "Ram sử dụng : " . (memory_get_usage() / 1024 / 1024) . " (Mb)";*/
?>