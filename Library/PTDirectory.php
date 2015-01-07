<?php
class PTDirectory
{
	/**
	 * Lấy ra toàn bộ thư mục con và đường dẫn đến thư mục đó
	 * @param string Đường dẫn đến thư mục cần kiểm tra
	 * @return array $folder=>$path
	 */
	public static function getSubDirectories($startdir, $ignoredDir = array())
	{
		$arrDir = array(); //Mang chua ten thu muc va duong dan den thu muc $foler => $path
		$startdir .= DIRECTORY_SEPARATOR;
        $listIgnored = array('.', '..','.svn');
        if(!empty($ignoredDir))
        {
            foreach($ignoredDir as $dir)
            {
                $listIgnored[] = $dir;
            }
        }
		if (is_dir($startdir)) {
			$dh = opendir($startdir);
			if ($dh) {
				while (($folder = readdir($dh)) !== false) {
					if (! (array_search($folder, $listIgnored, true) > - 1)) {
						if (filetype($startdir . $folder) == "dir")
							$arrDir[$folder] = $startdir . $folder;
					}
				}
				closedir($dh);
			}
		}
		return $arrDir;
	}
	
	/**
	 * Lấy toàn bộ file trong thư mục
	 */
	public static function getFilesDir($dir)
	{
		//check dir
		if (!is_dir(Url::getAppDir() . $dir))
			return false;
		$list = scandir(Url::getAppDir().$dir);
		$ignoredItem = array('.', '..','.svn');
		$arrItem = array();
		foreach ($list as $item)
		{
			if (!(array_search($item, $ignoredItem, true) > -1))
			{
				//$item = substr($item, 0, -4);
				$arrItem[$item] = $item;
			}
		}
		return $arrItem;
	}

    public static function emptyDir($dir, $delMe)
    {
        if(!$dh = @opendir($dir)) return;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..') continue;
            if (!@unlink($dir.'/'.$obj)) VccDirectory::emptyDir($dir.'/'.$obj, true);
        }
        closedir($dh);
        if ($delMe){
            @rmdir($dir);
        }
    }

    /**
     * Hỗ trợ tạo thư mục
     * @param string $path
     */
    public static function createDir($path){
        $cpath = "";
        $path = str_replace("\\", "/", $path);
        $pathArr = explode("/", $path);
        foreach($pathArr as $p){
            if(DIRECTORY_SEPARATOR != '/' && empty($cpath))
                $cpath = $p;
            else
                $cpath .= DIRECTORY_SEPARATOR . $p;
            if (!is_dir($cpath))
                if(!mkdir($cpath))
                {

                }
        }
        return true;
    }

    /**
     * calc directory size
     */
    public static function getDirSize($directory)
    {
        $dirSize=0;

        if(!$dh=opendir($directory))
        {
            return false;
        }

        while($file = readdir($dh))
        {
            if($file == "." || $file == "..")
            {
                continue;
            }

            if(is_file($directory.DIRECTORY_SEPARATOR.$file))
            {
                $dirSize += filesize($directory.DIRECTORY_SEPARATOR.$file);
            }

            if(is_dir($directory."/".$file))
            {
                $dirSize += self::getDirSize($directory.DIRECTORY_SEPARATOR.$file);
            }
        }

        closedir($dh);
        return $dirSize;
    }
}