<?php
/**
 * ho tro xu ly ngon ngu
 */

class PTLanguage {
    /**
     * Get resource language
     */
    public static function loadResource()
    {
        if(MULTI_LANGUAGE)
        {
            if(!isset($_SESSION['langcode']))
            {
                //load default language
                if(empty($_SESSION['langcode']))
                {
                    require_once 'Application/Modules/LanguagesCP/Models/Language.php';
                    $modelLang = new Models_Language();
                    $_SESSION['langcode'] = $modelLang->db->select('lang_code')->where('isdefault',1)->getField();
                }
            }
            $langFile = Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'LanguageResource_' . $_SESSION['langcode'] . '.php';
            //Kiểm tra xem đã có trong runtime hay không
            if(!file_exists($langFile))
            {
                $arrLang = array();
                //Load resource
                if(file_exists(Url::getAppDir().'Languages' . DIRECTORY_SEPARATOR . $_SESSION['langcode'] . '.xml'))
                    $arrLang = Xml::toArray(Url::getAppDir().'Languages'. DIRECTORY_SEPARATOR . $_SESSION['langcode'] . '.xml');
                file_put_contents($langFile, '<?php $language = ' . var_export($arrLang,true) . '; ?>');
            }
        }
    }

    /**
     * Get resource
     */
    public static function getResource()
    {
        $langFile = Url::getAppDir() . RUNTIME_DIR . DIRECTORY_SEPARATOR . 'LanguageResource_' . $_SESSION['langcode'] . '.php';
        if(file_exists($langFile))
        {
            require $langFile;
            return $language;
        }
        else
            return array();
    }
}