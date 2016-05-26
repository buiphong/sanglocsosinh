<?php
/**
 * Lớp html, hỗ trợ hiển thị, render action...
 * @author buiphong
 */
class Html
{
	/**
	 * Router hệ thống
	 * @var Router
	 */
	private $_router;

    /**
     * @var Controller_Abstract
     */
    private $_controller;

	var $rewriteEnable = true;
    var $_loadTemplate = false; //false|true
	
	function __construct($router = null, $controller = null)
	{
		if (!is_null($router))
			$this->_router = $router;
        if (!is_null($controller))
            $this->_controller = $controller;
	}
	
	/**
	 * overload method
	 */
	function __call($method, $arguments)
	{
        //change value $mode
		if ($method == 'renderAction' && count($arguments) == 1)
		{
			return $this->renderAction1($arguments[0]);
		}
		elseif ($method == 'renderAction' && count($arguments) == 2)
		{
			if (is_array($arguments[1]))
				return $this->renderAction2($arguments[0], $arguments[1]);
			elseif(!empty($arguments[1]))
				return $this->renderAction3($arguments[0], $arguments[1]);
            else
                return $this->renderAction1($arguments[0]);
		}
		elseif ($method == 'renderAction' && count($arguments) == 3)
		{
			if (is_array($arguments[2]))
				return $this->renderAction4($arguments[0], $arguments[1], $arguments[2]);
			else
				return $this->renderAction5($arguments[0], $arguments[1], $arguments[2]);
		}
		elseif ($method == 'renderAction' && count($arguments) == 4)
		{
            if(is_array($arguments[3]))
			    return $this->renderAction6($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
            else
                return $this->renderAction8($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
		}
        elseif ($method == 'renderAction' && count($arguments) == 5)
        {
            return $this->renderAction7($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
        }
		elseif ($method == 'actionResult' && count($arguments) == 3)
		{
			if(is_array($arguments[2]))
			{
				return $this->actionResult2($arguments[0], $arguments[1], $arguments[2]);
			}
			else 
				return $this->actionResult3($arguments[0], $arguments[1], $arguments[2]);
		}
		elseif ($method == 'actionResult' && count($arguments) == 4)
		{
            if(is_array($arguments[3]))
                return $this->actionResult4($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
			else
                return $this->actionResult6($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
		}
        elseif ($method == 'actionResult' && count($arguments) == 5)
        {
            return $this->actionResult5($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
        }
		elseif ($method == 'renderCPAction' && count($arguments) == 5)
		{
			return $this->renderCPAction5($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
		}
        elseif ($method == 'renderCPAction' && count($arguments) == 6)
        {
            return $this->renderCPAction6($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
        }
	}
	
	function renderAction1($action)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array('loadTemplate' => $this->_loadTemplate);
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function renderAction2($action, $params)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->args = $params;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array('loadTemplate' => $this->_loadTemplate);
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function renderAction3($action, $controller)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array('loadTemplate' => $this->_loadTemplate);
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function renderAction4($action, $controller, $params)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->args = $params;
		//Delete all include path
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		//Execute request
		$controller = new Controller_Front($router);
		$params = array('loadTemplate' => $this->_loadTemplate);
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function renderAction5($action, $controller, $module)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array('loadTemplate' => $this->_loadTemplate);
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function renderAction6($action, $controller, $module, $params)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
		$router->args = $params;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array('loadTemplate' => $this->_loadTemplate);
		return $controller->dispatch($router, 'Action', $params);
	}

    function renderAction7($action, $controller, $module, $alias, $params)
    {
        $router = $this->_router->getInstant();
        $router->alias = $alias;
        $router->action = $action;
        $router->controller = $controller;
        $router->module = $module;
        $router->args = $params;
        set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
        $controller = new Controller_Front($router);
        $params = array('loadTemplate' => $this->_loadTemplate);
        return $controller->dispatch($router, 'Action', $params);
    }

    function renderAction8($action, $controller, $module, $alias)
    {
        $router = $this->_router->getInstant();
        $router->alias = $alias;
        $router->action = $action;
        $router->controller = $controller;
        $router->module = $module;
        set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
        $controller = new Controller_Front($router);
        $params = array('loadTemplate' => $this->_loadTemplate);
        return $controller->dispatch($router, 'Action', $params);
    }
	
	function actionResult2($action, $controller, $params)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->args = $params;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array();
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function actionResult3($action, $controller, $module)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array();
		return $controller->dispatch($router, 'Action', $params);
	}
	
	function actionResult4($action, $controller, $module, $params)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
		$router->args = $params;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		$params = array();
		return $controller->dispatch($router, 'Action', $params);
	}

    function actionResult5($action, $controller, $module, $alias, $params)
    {
        $router = $this->_router->getInstant();
        $router->alias = $alias;
        $router->action = $action;
        $router->controller = $controller;
        $router->module = $module;
        $router->args = $params;
        set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
        $controller = new Controller_Front($router);
        $params = array();
        return $controller->dispatch($router, 'Action', $params);
    }

    function actionResult6($action, $controller, $module, $alias)
    {
        $router = $this->_router->getInstant();
        $router->alias = $alias;
        $router->action = $action;
        $router->controller = $controller;
        $router->module = $module;
        set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
        $controller = new Controller_Front($router);
        $params = array();
        return $controller->dispatch($router, 'Action', $params);
    }
	
	function renderCPAction1($dispathParams, $action)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "Library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		return $controller->dispatch($router, 'Action', $dispathParams);
	}
	
	function renderCPAction5($dispathParams, $action, $controller, $module, $params)
	{
		$router = $this->_router->getInstant();
		$router->action = $action;
		$router->controller = $controller;
		$router->module = $module;
		$router->args = $params;
		set_include_path(ROOT_PATH . PATH_SEPARATOR . "library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
		$controller = new Controller_Front($router);
		return $controller->dispatch($router, 'Action', $dispathParams);
	}

    function renderCPAction6($dispathParams, $action, $controller, $module, $alias, $params)
    {
        $router = $this->_router->getInstant();
        $router->alias = $alias;
        $router->action = $action;
        $router->controller = $controller;
        $router->module = $module;
        $router->args = $params;
        set_include_path(ROOT_PATH . PATH_SEPARATOR . "library" . PATH_SEPARATOR . ROOT_PATH . PATH_SEPARATOR . "Application");
        $controller = new Controller_Front($router);
        return $controller->dispatch($router, 'Action', $dispathParams);
    }

    function genMultiCheckboxes($name, $rs, $statusValues="",$disabled = ""){
        if (!$rs) return "";
        $tmp = "<table border=0>";
        $statusArr = explode(",", $statusValues);
        while (!$rs->EOF){
            $rowArr = $rs->fields;
            $c0 = 0; $c1 = 1; if (count($rowArr)<2) $c1 = 0;
            $tmp.="<tr><td width=5><"."input class='field' type='checkbox' name='$name"."[]' value='".$rowArr['id']."'";
            if (in_array($rowArr['id'], $statusArr)) $tmp.=" checked";
            if ($disabled == ""){
                $tmp.="></td><td style=\"color:darkblue\">".$rowArr['name']; $tmp.="</td></tr>";
            }else{
                $tmp.=" disabled></td><td style=\"color:darkblue\">".$rowArr['name']; $tmp.="</td></tr>";
            }
            $rs->MoveNext();
        }
        $tmp.= "</table>";
        return $tmp;
    }

    function genCheckbox($name, $v, $status=false){
        $tmp="<span><"."input class='field' type='checkbox' name='$name' id='$name' value='$v'";
        if ($status) $tmp.=" checked"; $tmp.="></span>";
        return $tmp;
    }

    /*
     * Gen multicheckbox from recordset
     */
    function genMultiCheckboxesFromRs($name, $array, $statusValues="",$disabled = "", $valueId='', $textId=''){
        $tmp = "";
        $statusArr = explode(",", $statusValues);
        foreach ($array as $rowArr)
        {
            if ($valueId != '' && $textId != '')
            {
                $c0 = $textId; $c1 = $valueId;
            }
            else
            {
                $c0 = 0; $c1 = 1; if (count($rowArr)<2) $c1 = 0;
            }
            $tmp.= "<li><span><"."input type='checkbox' name='$name"."[]' value='".$rowArr[$c1]."'";
            if (in_array($rowArr[$c1], $statusArr)) $tmp.=" checked";
            if($disabled == ""){
                $tmp.="> ".$rowArr[$c0]."</span></li>\n";
            }else{
                $tmp.=" disabled> ".$rowArr[$c0]."</span></li>\n";
            }
        }
        return $tmp;
    }//

    function genRadio($name, $arr, $val = '', $onclick=""){
        $ret = "";
        if (!is_array($arr)) return "";
        foreach($arr as $v=>$t){
            if (is_array($t))
                $t = $t['text'];
            $ret.= "<label class='radio inline-block'><input name='$name' type='radio' value='$v'";
            if ((string)$v==(string)$val){
                $ret.=" checked";
                $t = '<span class="checked">' . $t . '</span>';
            }
            else
                $t = '<span>' . $t . '</span>';
            if ($onclick!="") $ret.=" onclick='$onclick'";
            $ret.=">".$t.'</label>';
        }//end for
        return $ret;
    }

    /**
     * Tạo select
     */
    public function genSelect($name, $arrOptions, $value = null, $valueID = null, $nameID = null, $attrs = array('class' => 'chosen chosen-select'), $defaultText = '', $showEmptyVal = false)
    {
        if (!empty($attrs['id']))
            $id = $attrs['id'];
        else
            $id = $name;

        if ($attrs !== null && is_array($attrs)) {
            $at = '';
            foreach ($attrs as $key => $val)
            {
                $at .= $key . '="'.$val.'"';
            }
            $html = "<select name='$name' id='$id' $at >";
        }
        else {
            $html = "<select name='$name' id='$id'>";
        }
        if ($showEmptyVal)
            $html .= '<option value="">'.$defaultText.'</option>';
        if(!empty($arrOptions))
        {
            if(strpos($value,',') !== false)
                $value = explode(',', $value);
            else
                $value = array($value);
            foreach ($arrOptions as $key => $option) {
                //Nếu không có valueID và nameID
                if ($valueID == null || $nameID == null)
                    if (in_array($key, $value))
                        $html .= '<option value="' . $key . '" selected="selected">' . $option . '</option>';
                    else
                        $html .= '<option value="' . $key . '">' . $option . '</option>';
                else
                    if (in_array($option[$valueID], $value))
                        $html .= '<option value="' . $option[$valueID] . '" selected="selected">' . $option[$nameID] . '</option>';
                    else
                        $html .= '<option value="' . $option[$valueID] . '">' . $option[$nameID] . '</option>';
            }
        }
        $html .= "</select>";
        return $html;
    }

    public static function getCaptcha(){
        $path="http://".$_SERVER['HTTP_HOST'] . Url::getAbsoluteUrl() ."/Packages/securimage/securimage_show.php";
        return $path;
    }

    function treeJS($treeId){
        $str = "<script language='javascript'>\n";
        $str.= "$(function(){\n";
        $str.= "\t $(\"#$treeId\").treeview();\n";
        $str.= "});\n";
        $str.= "</script>\n";
        return $str;
    }

    function validateCaptcha($captcha){
        require_once(Url::getAppDir() . "Packages/securimage/securimage.php");
        $img = new Securimage();
        return $img->check($captcha);
    }

    /**
     * tạo select cho phần order
     */
    function getCbmOrder($name, $total, $selected = 0)
    {
        $arr = array();
        if($total >= 1)
            for($i = 1; $i <= $total; $i++)
                $arr[$i] = $i;
        return $this->genSelect($name, $arr, $selected);
    }
}