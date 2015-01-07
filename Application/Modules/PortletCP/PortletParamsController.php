<?php
class PortletParamsController extends Controller
{
	/**
	 * Kiểu tham số portlet
	 */
	public $type = array('blog_category' => 'Danh mục Blog', 'advertisement_zone' => 'Vùng quảng cáo', 'news_category' => 'Danh mục tin', 'detail_news' => 'Chi tiết tin bài', 
			'rs_category' => 'Danh mục nguồn tin ngoài','artical' => 'Trang nội dung',
            'product_category' => 'Danh mục sản phẩm',
			'videoID' => 'Danh sách video','videoAlbum'=>'Video album',
			'audioID' => 'Danh sách audio','imgCategory' => 'Danh mục album ảnh',
			'imageAlbum' => 'Album ảnh','product_detail' => 'Chi tiết sản phẩm', 'special_ptype' => 'Loại sản phẩm sắp xếp',
			'menu_type' => 'Loại menu','menu' => 'Menu','nav_type' => 'Navigation type','mem_type'=>'Loại thành viên',
			'option' => 'Mảng lựa chọn', 'value' => 'Giá trị','news_special_type'=>'Loại tin sắp xếp',
            'documents_category'=>"Loại tài liệu",
            'faq_type' => 'Nhóm Faq','class_id'=>'Loại Class','slider_type'=>'Loại Slider','res_sgroup' => 'Nhà hàng - Thực đơn sắp xếp',
            'stype_topic' => 'Loại sắp xếp chủ đề', 'stpye_shop' => 'Loại sắp xếp gian hàng');
	
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Danh sách portlet
	 */
	public function listAction()
	{
		$pageSize = 20;
	
		if (empty($this->params['page']))
			$page = 1;
		else
			$page = $this->params['page'];
		$offset = ($page - 1) * $pageSize;
		
		$model = new Models_PortletParam();
		
		$modelPortlet = new Models_Portlet();

		if (!empty($this->params['portletid']))
		{
			$model->db->where('portlet_id', $this->params['portletid']);
			$this->tpl->assign('portlet', $modelPortlet->db->select('title')->where('id', $this->params['portletid'])
					->getOne());
		}
	
		$recCount = $model->db->count();
		$params = $model->db->orderby('name')->limit($pageSize, $offset)->getAll();
		if ($params == false)
			$model->db->error;
		foreach ($params as $param)
		{
			$this->tpl->assign('editLink', $this->url->action('edit',
					array('portletid' => $this->params['portletid'], 'key' => $param['id'])));
			$param['type'] = $this->type[$param['type']];
			$this->tpl->insert_loop('main.param', 'param', $param);
		}
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $recCount));
		if ($recCount > 0)
			$this->tpl->parse('main.button');
	
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('createLink', $this->url->action('create', $this->params));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
	
		return $this->view();
	}
	
	/**
	 * Thêm mới portlet
	 */
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('listLink', $this->url->action('list', array('groupid' => @$this->params['groupid'], 'portletid' => @$this->params['portletid'])));
		$this->tpl->assign('paramType', $this->html->genSelect('type', $this->type));
        $this->tpl->assign('form_action', $this->url->action('create'));
		$this->tpl->assign('portlet_id', @$this->params['portletid']);
		return $this->view();
	}
	
	public function createPost(Models_PortletParam $model)
	{
		if ($model->Insert()) {
			if (!empty($model->portlet_id))
				$this->url->redirectAction('list', array('portletid' => $model->portlet_id));
			else
				$this->url->redirectAction('list');
		}
		else
			$this->showError('Mysql Error', $model->error);
	}
	
	/**
	 * Sửa portlet
	 */
	public function editAction()
	{
		$model = new Models_PortletParam($this->params['key']);
		$this->tpl->assign('form_action', $this->url->action('edit')); //, array('portletid' => $model->portlet_id)
		$this->tpl->assign('listLink', $this->url->action('list', array('portletid' => $model->portlet_id)));
		$this->tpl->assign('portlet_id', $model->portlet_id);
		$this->tpl->assign('paramType', $this->html->genSelect('type', $this->type, $model->type));
		return $this->view($model);
	}
	
	public function editPost(Models_PortletParam $model)
	{
		if ($model->Update())
			$this->url->redirectAction('list', array('portletid' => $model->portlet_id));
		else
			$this->showError('Mysql Error', $model->error);
	}
	
	
	public function deleteAjax()
	{
        $model = new Models_PortletParam();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->db->Delete('portlet_params', "id=$id"))
				{
					return json_encode(array('success' => false, 'msg' => $model->ErrorMsg()));
					break;
				}
		}
		elseif ($ids != '')
		{
			if(!$model->db->Delete('portlet_params', "id=$ids"))
			{
				return json_encode(array('success' => false, 'msg' => $model->ErrorMsg()));
			}
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('list')));
	}
	
	public function getValueBox($param, $valueParams = '')
	{
		$value = '';
		$name = $param['name'];
		$arrValue = array();
		if (!empty($valueParams))
		{
			$arr1 = explode('&', $valueParams);
			foreach ($arr1 as $item)
			{
				$a = explode('=', $item);
				$arrValue[$a[0]] = $a[1];
			}
		}
		switch ($param['type'])
		{
			case 'advertisement_zone':
				//Lấy danh sách danh mục tin
				$this->loadModule("AdvertiseCP");
				$model = new Models_AdsZone();
				$arrZone = $model->db->select('id,name')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrZone,@$arrValue[$name], 'id', 'name');
				break;
			case 'blog_category':
				//Lấy danh sách danh mục tin
				$this->loadModule("BlogCP");
				$model = new Models_BlogCategory();
				$arrZone = $model->getTreeCategory($parentId=0, $default = true, $langId='vi-VN');
				$value = $this->html->genSelect($name, $arrZone,@$arrValue[$name]);
				break;
			case 'news_category':
				//Lấy danh sách danh mục tin
                $this->loadModule('NewsCP');
				$modelCat = new Models_NewsCategory();
				$arrCat = $modelCat->getTreeCategory(0, false);
				$value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], null, null,array('class' => 'chosen-select'), '-----', true);
				break;
			case 'audioID':
				//Lấy danh sách audio
                $model = new Models_AudioAlbum();
				$arrNews = $model->db->select('id,title')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrNews,@$arrValue[$name], 'id', 'title');
				break;
			case 'videoID':
				//Lấy danh sách video
                $this->loadModule('GalleryCP');
                $model = new Models_Videos();
				$arrNews = $model->db->select('id,name')->where('status',1)->getFieldsArray();
				$value = $this->html->genSelect($name, $arrNews,@$arrValue[$name], 'id', 'name');
				break;
			case 'videoAlbum':
				//Lấy danh sách video
                $this->loadModule('GalleryCP');
				$model = new Models_VideoAlbum();
				$arrNews = $model->getTreeAlbum(0, false);
				//$arrNews = $model->db->select('id,name')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrNews,@$arrValue[$name]);
				break;
			case 'imageAlbum':
				//Lấy danh sách audio
                $this->loadModule('GalleryCP');
				$model = new Models_ImageAlbum();
				$value = $this->html->genSelect($name, $model->getTreeAlbum(0, true),@$arrValue[$name]);
				break;
            case 'imgCategory':
                //Danh sách danh mục album ảnh
                $this->loadModule('GalleryCP');
                $value = $this->html->genSelect($name, Models_ImgAlbumCategory::getTreeCat(), @$arrValue[$name]);
                break;
			case 'detail_news':
				//Lấy danh sách tin bài
                $this->loadModule('NewsCP');
                $model = new Models_News();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$arrNews = $model->db->select('id,title')->where('status',1)
                                    ->orderby('published_date', 'desc')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrNews,@$arrValue[$name], 'id', 'title');
				break;
			case 'artical':
				$model = new Models_Artical();
				//Lấy danh sách trang nội dung
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$articals = $model->db->select('id,title')->getFieldsArray();
				$value = $this->html->genSelect($name, $articals, @$arrValue[$name], 'id', 'title');
				break;
			case 'rs_category':
				//Lấy danh mục tin ngoài
				$cats = $this->db->getFieldsArray("select id, name from rs_category 
						where lang_code='".$_SESSION['sys_langcode']."' order by orderno asc");
				$value = $this->html->genSelect($name, $cats, @$arrValue[$name], 'id', 'name');
				break;
            case 'stype_topic':
                $this->loadModule('NewsTopicCP');
                $model = new Models_NewsTopicSType();
                $arr = $model->db->select('id,title')->getFieldsArray();
                $value = $this->html->genSelect($name, $arr, @$arrValue[$name], 'id', 'title');
                break;
            case 'stpye_shop':
                $this->loadModule('ECShopCP');
                $model = new Models_ECShopSType();
                $arr = $model->db->select('id,name')->getFieldsArray();
                $value = $this->html->genSelect($name, $arr, @$arrValue[$name], 'id', 'name');
                break;
			case 'product_category':
				//Lấy danh sách danh mục
                $this->loadModule('ECProductCP');
				$cat = new Models_ECProductCategory();
				$arrCat = $cat->getTreeCategory();
				$value = $this->html->genSelect($name, $arrCat,@$arrValue[$name]);
				break;
			case 'product_detail':
				
				break;
			case 'special_ptype':
                $this->loadModule('ECProductCP');
                $modelSPType = new Models_ECProductSpecialType();
				//Lấy danh sách loại sản phẩm sắp xếp
                if(!empty($_SESSION['langcode']))
                    $modelSPType->db->where('lang_code', $_SESSION['langcode']);
				$arrCat = $modelSPType->db->select('id,title')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], 'id', 'title');
				break;
			case 'menu_type':
				//Lấy danh sách loại menu
                $this->loadModule('MenuCP');
				$model = new Models_MenuType();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$arrType = $model->db->select("id,type_name")->getFieldsArray();
				$value = $this->html->genSelect($name, $arrType,@$arrValue[$name], 'id', 'type_name');
				break;
			case 'menu':
				//Lấy danh sách menu
                $this->loadModule('MenuCP');
				$model = new Models_Menu();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$arrType = $model->db->select("id,title")->getFieldsArray();
				$value = $this->html->genSelect($name, $arrType,@$arrValue[$name], 'id', 'title');
				break;
			case 'option':
				$arr1 = explode(',', $param['options']);
				$arrOption = array();
				foreach ($arr1 as $item)
				{
					$a = explode(':', $item);
					$arrOption[$a[0]] = $a[1];
				}
				$value = $this->html->genSelect($name, $arrOption,@$arrValue[$name]);
				break;
			case 'value':
				if (empty($arrValue[$name]))
					$arrValue[$name] = $param['options'];
				$value = "<input type='text' name='$name' id='$name' value='".$arrValue[$name]."'/>";
				break;
			case 'nav_type':
				//Lấy danh sách loại navigation
				$model = new Models_NavigationType();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$arrCat = $model->db->select('id,name')->orderby('name')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], 'id', 'name');
				break;
			case 'news_special_type':
				//Lấy danh sách loại tin sắp xếp
                $this->loadModule('NewsCP');
				$model = new Models_NewsSpecialType();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$arrCat = $model->db->select('id,title')
				        ->orderby('title')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], 'id', 'title');
				break;
			case 'documents_category':
				//Lấy danh sách loại tin sắp xếp
				$model = new Models_DocumentsCategory();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
				$arrCat = $model->db->select('id,title')
				        ->orderby('title')->getFieldsArray();
				$value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], 'id', 'title');
				break;
            case 'faq_type':
                //Lấy danh sách nhóm faq
                $this->loadModule('FaqCP');
                $model = new Models_FaqsCategory();
                //if(isset($_SESSION['langcode']))
                //    $model->db->where('lang_code', $_SESSION['langcode']);
                $arrCat = $model->db->select('id,name')
                    ->orderby('orderno')->getFieldsArray();
                $value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], 'id', 'name');
                break;
            case 'mem_type':
            //Lấy danh sách loại tin sắp xếp
            $this->loadModule('MembersCP');
            $model = new Models_MemberType();
            $arrCat = $model->db->select('id,name')->orderby('name')->getFieldsArray();
            $value = $this->html->genSelect($name, $arrCat,@$arrValue[$name], 'id', 'name');
                break;
            case 'class_id':
                //Lấy danh sách loại tin sắp xếp
                $this->loadModule('WebCP');
                $model = new Models_WebClass();
                $arrCat = $model->getTreeCategory();
                $value = $this->html->genSelect($name,$arrCat,@$arrValue[$name],'','',array());
                break;
            case 'slider_type':
                //Lấy danh sách loại tin sắp xếp
                $this->loadModule('SliderCP');
                $model = new Models_SliderType();
                $arrCat = $model->db->select('name,id')->orderby('order_no')->getFieldsArray();
                $value = $this->html->genSelect($name,$arrCat,@$arrValue[$name],'id','name');
                break;
            case 'res_sgroup':
                $this->loadModule('RestaurantCP');
                $model = new Models_ResMenuSpecialGroup();
                $arrCat = $model->db->select('title,id')->orderby('order_no')->getFieldsArray();
                $value = $this->html->genSelect($name,$arrCat,@$arrValue[$name],'id','title');
                break;
		}
		return $value;
	}
}