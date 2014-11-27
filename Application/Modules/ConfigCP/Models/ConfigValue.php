<?php
#[Table('config_value')]
#[PrimaryKey('id')]
class Models_ConfigValue extends PTModel
{
	public $id;
	
	public $config_code;
	
	public $value;
	
	public $status;

    public $lang_code;

    public $create_time;

    public $create_uid;

    public function getDefaultConfig()
    {
        if (empty($_SESSION['webConfig']) || DEBUG) {
            if(MULTI_LANGUAGE)
                $this->db->where('lang_code', $_SESSION['langcode']);
            $configs = $this->db->select('config_code,value')->getcFieldsArray();
            if($configs)
            {
                foreach ($configs as $conf)
                {
                    $_SESSION['webConfig'][$conf['config_code']] = $conf['value'];
                }
            }
            else
                $_SESSION['webConfig'] = array();
        }
        return $_SESSION['webConfig'];
    }

    /**
     * Lấy danh sách cấu hình
     */
    public static function getConfig($listCode)
    {
        $code = base64_decode($listCode);
        if(PTRegistry::isRegistered('config_' . $code))
            return PTRegistry::get('config_' . $code);
        else
        {
            $db = self::getInstance();
            if(isset($_SESSION['langcode']) && MULTI_LANGUAGE)
                $db->db->where('lang_code',$_SESSION['langcode']);

            $arr = $db->db->select('value,config_code')->where("instr('$listCode', config_code) > 0")->getcFieldsArray();
            foreach($arr as $v)
            {
                $data[$v['config_code']] = $v['value'];
            }
            PTRegistry::set('config_' . $code, $data);
            return $data;
        }
    }

    public static function getConfValue($confCode, $langCode='')
    {
        $fullCode = $confCode;
        if(MULTI_LANGUAGE)
        {
            if(empty($langCode))
                $langCode = @$_SESSION['sys_langcode'];
            $fullCode = $confCode . '_' . $langCode;
        }
        //Get value
        if(!PTRegistry::isRegistered('conf_' . $fullCode))
        {
            $obj = self::getInstance();
            if(MULTI_LANGUAGE)
            {
                if($langCode)
                    $obj->db->where('lang_code', $langCode);
            }
            PTRegistry::set('conf_' . $fullCode, $obj->db->select('value')->where('config_code', $confCode)->getField());
        }
        return PTRegistry::get('conf_' . $fullCode);
    }

    public function updateValue($code, $value)
    {
        $v = $this->db->select('id')->where('config_code', $code)->getField();
        if($v)
        {
            //update
            if($this->db->where('id', $v)->update(array('value' => $value)))
            {
                $fullCode = $code;
                //Change session config
                if(MULTI_LANGUAGE)
                    if(empty($_SESSION['sys_langcode']))
                        $fullCode = $code . '_' . $_SESSION['sys_langcode'];

                $_SESSION['webConfig'][$fullCode] = $value;
                return true;
            }
            else
                return false;
        }
        else
        {
            //add new
            $a = array('config_code' => $code, 'value' => $value,
                'create_time' => date('Y-m-d H:i:s'), 'create_uid' => $_SESSION['pt_control_panel']['system_userid']);
            if(MULTI_LANGUAGE)
                $a['lang_code'] = $_SESSION['sys_langcode'];
            if($this->Insert($a))
                return true;
            else
                return false;
        }
    }


}