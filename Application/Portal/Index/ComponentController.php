<?php
class ComponentController extends Presentation
{
    /**
     * header website
     */
	function __init(){
		$this->loadModule(array("NewsCP"));
	}

    /**
     * header trang
     * @return html
     */
    public function headerAction(){
        $this->tpl->assign('frmAction', $this->url->action('search', 'SearchComponent', 'Search'));
        return $this->view();
    }
    public function footerAction(){
        $this->tpl->assign('boxResEmail', $this->html->renderAction('boxResEmail'));
        return $this->view();
	}

    /**
     * Box đặt chỗ, tìm kiếm đầu trang
     */
    public function searchBookingAction()
    {
        $this->tpl->assign('frmAction', $this->url->action('searchBooking'));
        return $this->view();
    }

    public function searchBookingAjax(Models_ResBooking $model)
    {
        $model->created_time = date('Y-m-d H:i:s');
        if($model->Insert())
            return json_encode(array('success' => true, 'msg' => 'Đặt chỗ thành công!'));
        else
            return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi thực hiện đặt bàn.
            Xin quý khách vui lòng thực hiện lại sau, xin cảm ơn!'));
    }

    /**
	 * Lấy danh sách tin bài của danh mục
	 */
	function listNews($category = null, $number = 5){
		$modelNews = new Models_News();
		$news = $modelNews->db->select('id,title,url_title')->where('news.status', 1)->where("category_id", $category)->orderby('created_date', 'desc')->limit($number)->getcFieldsArray();
		return $news;
	}

    function getBreadCrumbAction()
    {
        $bCrumbs[] = array('title' => "Trang chủ", 'link' => $this->url->getApplicationUrl());
        $router = new Router();
        $router->analysisRequestUri();
        if ($router->module == 'Index' && $router->controller == 'Index' && $router->action == 'index' && isset($router->args['menu']))
        {
            $this->loadModule('MenuCP');
            $model = new Models_Menu();
            //menu
            $menu = $model->db->select('title,id,parentid,externallink,url_title')->where('url_title', $router->args['menu'])->getcFields();
            $parents = $model->getArrayParent($menu['parentid']); //Lay tu menu cha
            $strUrl = '';
            if($parents)
            {
                foreach($parents as $pmenu)
                {
                    $strUrl .= $pmenu['url_title'] . '/';
                    if(empty($menu['externallink']))
                        $pmenu['href'] = $this->url->action('index', 'Index', 'Index', array('menu1' => $strUrl, 'menu' => $pmenu['url_title']));
                    else
                        $pmenu['href'] = $pmenu['externallink'];
                    $bCrumbs[] = array(
                    'title' => $pmenu['title'],
                    'class' => 'item',
                    'link' => $pmenu['href']);
                }
                $strUrl = substr($strUrl, 0, -1);
            }
            if(empty($menu['externallink']))
                $menu['href'] = $this->url->action('index', 'Index', 'Index', array('menu1' => $strUrl, 'menu' => $menu['url_title']));
            else
                $menu['href'] = $menu['externallink'];
            if (!empty($menu))
                $bCrumbs[] = array(
                    'title' => $menu['title'],
                    'class' => 'item',
                    'link' => $menu['href']);
        }
        elseif ($router->module == 'News' && $router->controller == 'NewsComponent' && $router->action == 'detailNews')
		{
            $this->loadModule('NewsCP');
            $modelCat = new Models_NewsCategory();
            $id = @$router->args['id'];
            if(empty($id))
                $id = @$router->args['nid'];
            if($id)
            {
                //get link category
                $cat = $modelCat->db->select('news_category.id,news_category.title')
                    ->join('news', 'news.category_id=news_category.id')
                    ->where('news.id', $id)->getcFields();
                if (!empty($cat))
                {
                    $arrCat = $modelCat->getCatParent($cat['id']);
                    foreach($arrCat as $k => $v)
                    {
                        $bCrumbs[] = array(
                            'title' => $v['title'],
                            'class' => 'item',
                            'link' => NewsHelper::getLinkCat($v['id']));
                    }
                }
            }
        }
        elseif ($router->module == 'News' && $router->controller == 'NewsComponent' && $router->action == 'getListNews')
        {
            $this->loadModule('NewsCP');
            $modelCat = new Models_NewsCategory();
            if(!empty($router->args['ncatid']))
            {
                $arrCat = $modelCat->getCatParent($router->args['ncatid']);
                foreach($arrCat as $k => $v)
                {
                    $bCrumbs[] = array(
                        'title' => $v['title'],
                        'class' => 'item',
                        'link' => NewsHelper::getLinkCat($v['id']));
                }
            }
        }
        elseif ($router->module == 'Search')
        {
            $bCrumbs[] = array(
                'title' => $this->tpl->language['search'],
                'class' => 'item');
        }
        elseif ($router->module == 'Members' && $router->action == 'register')
        {
            $bCrumbs[] = array(
                'title' => 'Đăng ký thành viên',
                'class' => 'item');
        }
        elseif ($router->module == 'Members' && $router->action == 'profile')
        {
            $bCrumbs[] = array(
                'title' => 'Thành viên',
                'class' => 'item');
        }
        elseif ($router->module == 'Contact' && $router->action == 'contactPage')
        {
            $bCrumbs[] = array(
                'title' => 'Liên hệ',
                'class' => 'item');
        }

        $i = 1;
        $total = count($bCrumbs);
		foreach ($bCrumbs as $element)
        {
            $element['class'] = '';
            if($i == $total)
                $element['class'] = 'last';
            $this->tpl->insert_loop('main.element', 'element', $element);
            $i++;
        }
		return $this->view();
	}

	public function paggingAction(){
		$class = ' class="pagging-item"';
		//Bên hàm gọi chuyển params module thành router, thì khi bên này gọi, sẽ chuyển params['module'] thành params['router']
		$module = $this->params['router']->module;
		$controller = $this->params['router']->controller;
		$action = $this->params['router']->action;
		$page = @$this->params['page'];
		$pageSize = @$this->params['pageSize'];
		$totalRows = @$this->params['totalItem'];
        $customParams = @$this->params['cusParams'];
        if(!empty($customParams))
            $params = $customParams;
        else
		    $params = @$this->params['params'];
        //Unset params when render cp
        unset($params['m']);
        unset($params['c']);
        unset($params['a']);
        unset($params['menu']);
		if (empty($page) || empty($totalRows))
			return '';
		if (empty($pageSize))
			$pageSize = 20;
		$pagecount = intval($totalRows/$pageSize); if (($totalRows%$pageSize)>0) $pagecount++;
		if ($page > $pagecount) $page = $pagecount;
		$offset = ($page -1) * $pageSize;
		$setPageView = 10;//Số trang muốn hiển thị.
		$before = '';
		$start = 1;
		$end = $pagecount;
		if ($pagecount > 1){
			/*if($pagecount > $setPageView && $page !=1){
				$params[$this->params['pageParamName']] = 1;
				$url = $this->url->action($action, $controller, $module, $params);
				$begin = $pageHtml = '<li class="pager-next"><a href="'.$url.'" title="next" class="active">‹</a></li>';
				$this->tpl->assign('begin',$begin);
			}*/
			$this->tpl->assign('before',$before);
			//So sánh số trang hiện có so với số trang yêu cầu hiển thị
			if($pagecount < $setPageView){
				for($i=$start; $i <= $end; $i++){
					if($i == $page)
						$class = "active";
					else
						$class = '';				
					$params[$this->params['pageParamName']] = $i;
					$url = $this->url->action($action, $controller, $module, $params);
					$pageHtml = '<li class="pager-item"  class="'.$class.'"><a href="'.$url.'" title="'.$i.'">'.$i.'</a></li>';
					$this->tpl->insert_loop('main.pagging', 'page', $pageHtml);
				}
			}
			else{
				if($setPageView % 2 == 0){
					$front = $page + $setPageView /2 -1;
					$back = $page - $setPageView/2;
				}
				else{
					$front = $page + ($setPageView-1) /2;
					$back = $page - ($setPageView-1) /2;
				}
				if($page <= ceil($setPageView/2)){
					$front = $setPageView;
					$back = 1;
				}
				if($page >= $pagecount - $setPageView +1){
					$front = $pagecount;
					$back = $pagecount - $setPageView +1;
				}
				for($i= $back; $i < $page; $i++){
					$params[$this->params['pageParamName']] = $i;
					$url = $this->url->action($action, $controller, $module, $params);
					$pageHtml = '<li class="pager-item"><a href="'.$url.'" title="'.$i.'">'.$i.'</a></li>';
					$this->tpl->insert_loop('main.pagging', 'page', $pageHtml);
				}
				for($i=$page; $i <= $front; $i++){
					if($i == $page)
						$class = "active";
					else
						$class = '';
					$params[$this->params['pageParamName']] = $i;
					$url = $this->url->action($action, $controller, $module, $params);							
					$pageHtml = '<li class="pager-item" class="'.$class.'"><a href="'.$url.'" title="'.$i.'">'.$i.'</a></li>';
					$this->tpl->insert_loop('main.pagging', 'page', $pageHtml);
				}

			}
            if($pagecount > $setPageView){
                $end = '<div class="box_album_bt fl">';
                if($page !=1)
                {
                    $params[$this->params['pageParamName']] = $page -1;
                    $url = $this->url->action($action, $controller, $module, $params);
                    $end .= '<div class="bt_prev"><a href="'.$url.'" title="next">&nbsp;</a></div>';
                }
                if($page != $pagecount)
                {
                    $params[$this->params['pageParamName']] = $page + 1;
                    $url = $this->url->action($action, $controller, $module, $params);
                    $end .= '<div class="bt_next"><a href="'.$url.'" title="next">&nbsp;</a></div>';
                }
				$this->tpl->assign('end',$end);
			}
		}
		return $this->view();
	}
    public function tagAction()
    {
        return $this->view();
    }

    /**
     * Box button Đăng cây, mở gian hàng dành cho thành viên
     * @return html
     */
    public function boxActMemberAction()
    {
        if(!isset($_SESSION['member']['id']))
        {
            $this->tpl->assign('linkPostTree', "javascript:alert('Bạn cần đăng nhập trước')");
            $this->tpl->assign('linkAddShop', "javascript:alert('Bạn cần đăng nhập trước')");
        }
        else
        {
            $this->tpl->assign('linkPostTree', $this->url->action('postProduct', 'ShopComponent','Shop'));
            $this->tpl->assign('linkAddShop', $this->url->action('memberCreateShop', 'ShopComponent', 'Shop'));
        }
        return $this->view();
    }
}