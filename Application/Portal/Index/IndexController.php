<?php
class IndexController extends Presentation
{
	public function indexAction()
	{
		$this->loadModule("MenuCP");
		//$langCode = @$_SESSION['langcode'];
		$langCode = "vi-VN";
		$modelMenu = new Models_Menu();
        $modelMenu->db->join('menu_type', 'menu.type_id = menu_type.id');
		//Phân tích lấy menuid thông qua url_title menu
		if (!empty($this->params['menu'])){
            if(!empty($this->params['type']))
                $modelMenu->db->where('menu_type.url_title', $this->params['type']);
			$menu = $modelMenu->db->select('menu.*')->where('menu.url_title', $this->params['menu'])
                ->where('menu.lang_code', $langCode)->getFields();
            if(isset($menu['title']) && !empty($menu['title']))
                $this->viewParam->title = $menu['title'];
		}
		else{
			$menu = $modelMenu->db->where('(menu.parentid=0 or isnull(menu.parentid)=1)')
                ->where('menu.lang_code', $langCode)->where('menu.is_default', 1)->orderby('menu.orderno')
                ->getFields();
		}
        if(!$menu)
            die($this->tpl->language['not_found_menu']);
		//Get menu main portlet
		$this->menu = $menu;
        $_SESSION['sys_menu'] = array('id'=>$menu['id'],'title'=>$menu['title']);
		//$this->viewParam->title = $menu['title'];
		$this->template = $menu['template'];
		$this->layout = $menu['layout'];
		return $this->view();
	}

    public function testMailAction()
    {
        $mail = new VccMail();
        $mail->send('buiphongbg@gmail.com', 'IQMart mail', 'IQMart test gửi mail');
    }
}