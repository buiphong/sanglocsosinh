<?php
/**
 * Menu helper
 */
class MenuHelper
{
    /**
     * Get link menu
     */
    public static function getLink($menu,$menuType = array())
    {
        if(!is_array($menu))
            $menu = Models_Menu::getById($menu);
        elseif(empty($menu['title']) && $menu['id'])
            $menu = Models_Menu::getById($menu['id']);

        if(empty($menuType))
            $menuType = Models_MenuType::getById($menu['type_id']);

        $url = new Url();
        if(!empty($menu['externallink']))
            return $menu['externallink'];
        if(empty($menu['url_title']))
            $menu['url_title'] = String::seo($menu['title']);
        $arr = array(
            'menu' => $menu['url_title']
        );

        //Get path menu
        $path = explode('/',$menu['path']);
        $cPath = count($path);
        if($cPath > 1)
        {
            unset($path[$cPath - 1]);
            $model = new Models_Menu();
            foreach($path as $pid)
            {
                $model->db->where('id', $pid);
            }
            $list = $model->db->select('id,title,url_title')->orderby('path', 'asc')->getFieldsArray();
            $i = 1;
            foreach($list as $item)
            {
                $arr['menu' . $i] = $item['url_title'];
                $i++;
            }
        }
        if(isset($menuType['url_title']) && !empty($menuType['url_title']))
            $arr['type'] = $menuType['url_title'];
        return $url->action('index', 'Index', 'Index', $arr);
    }
}