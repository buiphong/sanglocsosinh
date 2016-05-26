<?php
class Cache_Frontend extends Cache_Abstract
{
	/**
	 * Đường dẫn đầy đủ tới file cache
	 * @var string
	 */
	private $file;
	
	public function __construct($time=NULL, $compresses=NULL, $prefix=NULL, $path=NULL)
	{
		//Lấy các giá trị được cấu hình từ config
		if($path !== NULL)
			$this->path = $path;
		else if(!$this->path)
			$this->path = "Cache/Frontend";
		//Kiểm tra đương dẫn
		if(!is_dir($this->path))
		{
			throw new Exception("Đường dẫn tới thư mục chứa Frontend Cache không chính xác: " . $path);
		}
	
		if($time !== NULL)
			$this->time = $this->convertCacheTime($time);
		else if(!$this->time)
			$this->time = $this->convertCacheTime("10m");
			
		if ($prefix !== NULL)
			$this->prefix = $prefix;
		else if(!$this->prefix)
			$this->prefix = "vcCf_";
			
		if ($compresses !== NULL)
			$this->compresses = $compresses;
		else if(!$this->compresses)
			$this->compresses = false;
	}
	
	/**
	 * Lưu cache
	 */
	public function save()
	{
		
	}
	
	/**
	 * Load cache
	 */
	public function load()
	{
		
	}
}