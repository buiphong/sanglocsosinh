<?php
abstract class Cache_Abstract
{
	/**
	 * Đường dẫn tới thư mục chứa cache
	 * @var string
	 */
	public $path;

	/**
	 * Prefix của tên file cache
	 * @var string
	 */
	public $prefix;
	
	/**
	 * Thời gian cache tính theo phút
	 * @var int
	 */
	public $time;
	
	/**
	 * Có sử dụng nén khi lưu cache hay không ?
	 * @var boolean
	 */
	public $compresses;
		
	/**
	 * Lấy ra thời gian cache
	 */
	public function getTime()
	{
		return $this->time;
	}
	
	/**
	 * Gán thời gian cache
	 * @param string $time VD: 10s, 10m, 10h
	 */
	public function setTime($time)
	{
		$this->time = $this->convertCacheTime($time);
	}
	
	/**
	 * Lấy ra đường dẫn tới thư mục chứa cache
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
	 * Gán đường dẫn tới thư mục chứa cache
	 * @param string $path
	 */
	public function setPath($path)
	{
		if(is_dir($path))
			$this->path = $path;
		else
		{
			throw new Exception("Đường dẫn tới thư mục chứa Output Cache không chính xác: " . $path);
		}
	}
	
	/**
	 * Lấy ra prefix của file cache
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	/**
	 * Gán prefix của file cache
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Chuyển định dạng thời gian từ kí tự thành số
	 * @param string $timestr VD: 10s, 10m, 10s
	 * @return int
	 */
	protected function convertCacheTime($timestr)
	{
		require_once 'VccDateTime.php';
		return VccDateTime::toTime($timestr);
	}
	
	/**
	 * Có nén dữ liệu khi lưu cache hay không ?
	 * @param boolean $bool
	 */
	public function compresses($bool)
	{
		$this->compresses = $bool;
	}
}