<?php
#[Table('templates')]
#[PrimaryKey('id')]
class Models_Template extends PTModel
{
	public $id;
	public $name;
	public $title;
	public $avatar;
	public $isdefault;
	public $createddate;
	public $createdby;
	public $userid;

    /**
     * get default template
     */
    public function getDefault()
    {
        $temp = $this->db->select('name')->where('isdefault', 1)->getField();
        //Check template
        return $temp;
    }

    /**
     * Get list template
     */
    public static function getTemplates()
    {
        $ignoredDir = array('.', '..', 'flatadmin');
        $listDir = VccDirectory::getSubDirectories((Url::getAppDir().'Templates'), $ignoredDir);
        foreach ($listDir as $key => $dir)
        {
            $list[$key] = array('name' => $key, 'path' => $dir);
        }
        return $list;
    }

    /**
     * Get list layout
     */
    public static function getLayout($template)
    {
        //check dir
        if (!is_dir(Url::getAppDir(). TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template))
            return false;
        $list = scandir(Url::getAppDir().TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template . '/layout');
        $ignoredItem = array('.', '..','.svn');
        $arrItem = array();
        foreach ($list as $item)
        {
            if (!(array_search($item, $ignoredItem) > -1))
            {
                $item = substr($item, 0, -4);
                $arrItem[$item]['name'] = $item;
                $arrItem[$item]['path'] = Url::getAppDir().TEMPLATE_DIR .
                    DIRECTORY_SEPARATOR . $template .DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . $item . '.htm';
            }
        }
        return $arrItem;
    }
}