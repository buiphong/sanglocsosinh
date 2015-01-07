<?php
class Attribute
{
	//Lấy thuộc tính cho property
	public static function getAttributeProperty($model, $property)
	{
		$ref = new ReflectionClass($model);
		$name = $ref->getName();
        if(isset($model->attributes[$name.'::$' . $property]))
        {
            $properties = $model->attributes[$name.'::$' . $property];
		    return $properties;
        }
        return false;
	}

    /**
     * save attribute to file
     */
    public static function saveAttributes($object, $attrs)
    {
        if(is_object($object))
            $object = get_class($object);
        $dir = ROOT_PATH . DIRECTORY_SEPARATOR . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Attributes';
        if(!is_dir($dir))
            PTDirectory::createDir($dir);
        $attrFile = $dir . DIRECTORY_SEPARATOR . base64_encode($object) . '.php';
        file_put_contents($attrFile, '<?php return ' . var_export($attrs,true) . '; ?>');
    }

    public static function getObjAttributes($object)
    {
        $attrFile = ROOT_PATH . DIRECTORY_SEPARATOR . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'Attributes' . DIRECTORY_SEPARATOR . base64_encode(get_class($object)) . '.php';
        if(file_exists($attrFile) && !DEBUG)
        {
            $result = include $attrFile;
            return $result;
        }
        else
        {
            //Phân tích comment lấy attribute
            $parser = new Attribute_Parser();
            $ref = new ReflectionClass($object);
            $path = $ref->getFileName();
            $attribute = $parser->parse($path);
            self::saveAttributes($object, $attribute);
            return $attribute;
        }
    }
}