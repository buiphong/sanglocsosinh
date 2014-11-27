<?php
#[Table('router')]
#[PrimaryKey('id')]
class Models_Router extends PTModel
{
	public $id;
	
	public $module;
	
	public $args;
	
	public $controller;
	
	public $action;
	
	public $template;
	
	public $layout;

    function getRouter($router)
    {
        if ($router)
        {
            //Get with params
            $values = $this->db->where('module', $router->module)->where('controller', $router->controller)
                                ->where('action', $router->action)->getcFieldsArray();
            $tP = count($values);
            if($tP > 1)
            {
                $select = reset($values);
                //order portlet
                for($i = 0; $i < $tP; $i++)
                {
                    if(!empty($values[$i]['args']))
                    {
                        $t1 = unserialize($values[$i]['args']);
                        $t1 = count($t1);
                    }
                    else
                        $t1 = 0;
                    for($j = $i+1; $j < $tP; $j++)
                    {
                        if(!empty($values[$j]['args']))
                        {
                            $t2 = unserialize($values[$j]['args']);
                            $t2 = count($t2);
                        }
                        else
                            $t2 = 0;
                        if($t2 > $t1)
                        {
                            $tmp = $values[$i];
                            $values[$i] = $values[$j];
                            $values[$j] = $tmp;
                        }
                    }
                }
                $tmp = end($values);
                if(!empty($tmp['args']))
                    $tmp = false;
                foreach($values as $p)
                {
                    $args = unserialize($p['args']);
                    if($args)
                    {
                        ksort($router->args);
                        if(count($args) <= count($router->args))
                        {
                            $valid = true;
                            foreach($args as $k => $v)
                            {
                                if(!isset($router->args[$k]) || $router->args[$k] != $v)
                                    $valid = false;
                            }
                            if($valid)
                            {
                                $tmp = $p;
                                break;
                            }
                        }
                    }
                }
                $values = $tmp;
            }
            else
            {
                $values = reset($values);
                //check args
                if(!empty($values['args']))
                {
                    $arr = unserialize($values['args']);
                    foreach($arr as $k => $v)
                    {
                        $k = str_replace("'", '', $k);
                        if(!isset($router->args[$k]) || $router->args[$k] != $v)
                        {
                            return false;
                        }
                    }
                }
            }
            return $values;
        }
        return false;
    }
}