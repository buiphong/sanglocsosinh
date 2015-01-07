<?php
class PTRegistry extends ArrayObject
{
	/**
	 * Registry object provides storage for shared objects.
	 * @var PTRegistry
	 */
	private static $_registry = null;
	
	/**
	 * Initialize the default registry instance.
	 * @return void
	 */
	protected static function init()
	{
		self::setInstance(new PTRegistry());
	}
	
	/**
	 * Set the default registry instance to a specified instance.
	 */
	public static function setInstance(PTRegistry $registry)
	{
		if (self::$_registry !== null) {
			throw new Exception('Registry is already initialized');
		}
	
		//self::setClassName(get_class($registry));
		self::$_registry = $registry;
	}
	
	/**
	 * Retrieves the default registry instance.
	 * @return PTRegistry
	 */
	public static function getInstance()
	{
		if (self::$_registry === null) {
			self::init();
		}
	
		return self::$_registry;
	}
	
	/**
	 * set vars
	 * @param unknown $index
	 * @param unknown $value
	 */
	public static function set($index, $value)
	{
		$instance = self::getInstance();
		$instance->offsetSet($index, $value);
	}
	
	public static function get($index)
	{
		$instance = self::getInstance();
	
		if (!$instance->offsetExists($index)) {
			throw new Exception("No entry is registered for key '$index'");
		}
	
		return $instance->offsetGet($index);
	}
	
	/**
	 * Returns TRUE if the $index is a named value in the registry,
	 * or FALSE if $index was not found in the registry.
	 *
	 * @param  string $index
	 * @return boolean
	 */
	public static function isRegistered($index)
	{
		if (self::$_registry === null) {
			return false;
		}
		return self::$_registry->offsetExists($index);
	}
}