<?php
class MenuComponentController extends Presentation{
	function __init(){
	}

	public function showMenuAction(){
		//Lấy menu
		if(!empty($this->params['type']))
			$type_id = $this->params['type'];
		else
			$type_id = 1;
			
		$this->loadModule("MenuCP");
		$modelType = new Models_MenuType();
		$menuType = $modelType->db->select('type_name')->where('id',$type_id)->getcField();
		$this->tpl->assign('menuType',$menuType);
		$menus = Models_Menu::getMenuMultiLevel($type_id);
		$i = 1;
        $total = count($menus);
		foreach ($menus as $menu){
            $item = $menu['values'];
            $item['icon'] = '';
			if($item['iconpath'])
                $item['iconpath'] = $this->url->getContentUrl($item['iconpath']);
			if($item['iconpath_hover'])
                $item['iconpath_hover'] = $this->url->getContentUrl($item['iconpath_hover']);

            $item['href'] = MenuHelper::getLink($item);
            $item['class'] = '';
            if(!empty($item['iconpath']))
                $item['class'] = 'a_home';
            if($i < $total)
                $this->tpl->parse('main.menu.separator');
			if(isset($menu['subs']) && !empty($menu['subs']))
			{
				foreach($menu['subs'] as $listMenu){
                    $listMenu['values']['href'] = MenuHelper::getLink($listMenu['values']);
                    if(isset($listMenu['subs']) && !empty($listMenu['subs']))
                    {
                        foreach($listMenu['subs'] as $listChildMenu){
                            $listChildMenu['values']['href'] = MenuHelper::getLink($listChildMenu['values']);
                            $this->tpl->insert_loop('main.menu.child.menu_child.has_child.has_menu_child', 'has_menu_child', $listChildMenu['values']);
                        }
                        $this->tpl->parse('main.menu.child.menu_child.has_child');
                    }

					$this->tpl->insert_loop('main.menu.child.menu_child', 'menu_child', $listMenu['values']);
				}
				$this->tpl->parse('main.menu.child');
			}
	
			$this->tpl->insert_loop('main.menu', 'menu', $item);
            $i++;
		}
		return $this->view();
	}
}