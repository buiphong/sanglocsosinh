<?php
define("LANG", "vi-VN");
//Debug mode, disable caching
define('DEBUG', false);
//Setting compress js,css
define('COMPRESS_CSS',true);
define('COMPRESS_JS',false);

define('MULTI_LANGUAGE', false);

define('CACHETIME', 0);//tính bằng giây
define('HTML_CACHETIME', 0); //Thời gian cache html
//Adodb cachedir
define('ADODB_CACHE_DIR', 'Data' . DIRECTORY_SEPARATOR .'Cache' . DIRECTORY_SEPARATOR . 'Adodb');
if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR))
    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR);

//Assets combine dir
define('COMBINE_ASSETS_DIR', 'Data' . DIRECTORY_SEPARATOR . 'Assets');
if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . COMBINE_ASSETS_DIR))
    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . COMBINE_ASSETS_DIR);

//Cache Frontend html dir
define('CACHE_FRONTEND_DIR', 'Data' . DIRECTORY_SEPARATOR .'Cache' . DIRECTORY_SEPARATOR . 'Frontend');
if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR))
    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR);

//Data index search
define('SEARCH_DIR', 'Data' . DIRECTORY_SEPARATOR .'IndexData');
if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . SEARCH_DIR))
    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . SEARCH_DIR);

//Template dir
define('TEMPLATE_DIR', 'Templates');
//Skin dir
define('SKIN_DIR', 'Skins');

//Thumbnail dir
define('THUMBNAIL_DIR', 'Data' . DIRECTORY_SEPARATOR . 'Thumbnail');
if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . THUMBNAIL_DIR))
    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . THUMBNAIL_DIR);
//Runtime dir
define('RUNTIME_DIR', 'Data' . DIRECTORY_SEPARATOR . 'Runtime');
if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . RUNTIME_DIR))
    PTDirectory::createDir(ROOT_PATH . DIRECTORY_SEPARATOR . RUNTIME_DIR);


//Config default router
$defaultRouter = array(
    'alias' => 'Portal',
    'module' => 'Index',
    'controller' => 'Index',
    'action' => 'index');

//Database config
$dbConfig = array(
	'mysql' => array(
			'server' => 'localhost',
			'username' => 'nhsanf6k_admin',
			'port' => '3306',
			'password' => 'slss@bionet@)!$',
			'dbname' => 'nhsanf6k_website',
	)
	/*'mongodb' => array(
			'server' => '172.16.2.99',
			'username' => '',
			'port' => '27017',
			'password' => '',
			'dbname' => 'vietclever-ver2',
	)*/
);
?>
