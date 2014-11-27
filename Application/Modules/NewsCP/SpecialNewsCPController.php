<?php
class SpecialNewsCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Hiển thị danh sách tin đặc biệt
	 */
	public function indexAction()
	{
		$modelNewcat = new Models_NewsCategory();
		$modelSpecialType = new Models_NewsSpecialType();
		$modelSpecial = new Models_NewsSpecial();
		//Danh sách tin sắp xếp
        if(!empty($_SESSION['sys_langcode']))
		    $modelSpecial->db->where('lang_code', $_SESSION['sys_langcode']);
		if (!empty($this->params['catid']))
		{
			$catId = $this->params['catid'];	
			$modelSpecial->db->where('category_id', $catId);
			$this->tpl->assign('catName', '- ' . $modelNewcat->db->select('title')->where('id',$catId)->getOne());
            $this->tpl->assign('catid', $catId);
		}
		if(!empty($this->params['type']))
		{
			$type = $modelSpecialType->db->select('id,title')->where('id', $this->params['type'])
                ->orderby('title')->getcFields();
            $this->tpl->assign('type', $type['id']);
			$this->tpl->assign('catName', '- ' . $type['title']);
		}
        else
        {
            $type = $modelSpecialType->db->select('id,title')->orderby('title')->getcFields();
            $this->tpl->assign('type', $type['id']);
            $this->tpl->assign('catName', '- ' . $type['title']);
        }
		//Danh mục tin
		$this->tpl->assign('sidebarCategory', $this->html->renderAction('sidebarSpecialType', 'SpecialNewsCP'));	

		//Hiển thị nút lấy tin đã chọn
		if (!empty($_SESSION['sys_selectedNews']))
		{
			$this->tpl->assign('catid', @$catId);
			$this->tpl->assign('typeid', $type['id']);
			$this->tpl->parse('main.seletedButton');
		}
	
		$this->tpl->assign('frmSearchAction', $this->url->action('index'));
		$this->tpl->assign('textSearch', @$this->params['search-text']);
		$this->tpl->assign('addLink', $this->url->action('create', $this->params));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('listNewsLink', $this->url->action('index', 'NewsCP'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('typeid', $type['id']);
		$this->setTitle('Tin bài sắp xếp');
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}

    public function listAjax()
    {
        $modelNewcat = new Models_NewsCategory();
        $modelSpecialType = new Models_NewsSpecialType();
        $modelSpecial = new Models_NewsSpecial();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];
        $type = @$this->params['type'];
        if(empty($type))
            $type = $modelSpecialType->db->select('id,title')->orderby('title')->getFields();
        else
            $type = $modelSpecialType->db->select('id,title')->orderby('title')->where('id', $type)->getFields();
        $modelSpecial->db->join('news_special_type', 'news_special.special_type=news_special_type.id');
        $modelSpecial->db->join('news', 'news_special.news_id=news.id');
        if(!empty($type))
        {
            $modelSpecial->db->where('news_special.special_type', $type['id']);
            $this->tpl->assign('catName', '- ' . $type['title']);
        }

        if(!empty($this->params['sSearch']))
            $modelSpecial->db->like('news_special.title', $this->params['sSearch']);

        /*Ordering*/
        /*if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $modelSpecial->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else*/
        $modelSpecial->db->orderby('news_special.orderno', 'asc');
        $totalRow = $modelSpecial->db->count()?$modelSpecial->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $modelSpecial->db->select('news_special.id,news_special.title,news_special.orderno,
            news_special.image_path,news_special.special_type,news_special.category_id,news_special_type.title as typeTitle')
            ->limit($pageSize,$offset)->getFieldsArray();
        if(!empty($datas))
        {
            foreach($datas as $key => $val)
            {
                $datas[$key]['title'] = "<a href=''>".$val['title']."</a>";
                $datas[$key]['btn'] = $this->html->renderAction('getHtmlBtn', array('snews' => $val));
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

	/**
	 * Sửa tin sắp xếp
	 */
	public function editAction()
	{
		if (!empty($this->params['id']))
		{
			//$news = $this->db->getFields("select * from news where id=" . $this->params['id']);
			$news = new Models_NewsSpecial($this->params['id']);
	
			//Lấy danh sách danh mục
			$this->tpl->assign('categoryId',
					$this->html->genSelect('category_id', $this->getTreeCategory(), $news->category_id,
							'', '', array('style' => 'width: 307px;', 'class' => 'field')));
            if(base64_decode($news->brief, true))
                $news->brief = base64_decode($news->brief);
			$this->tpl->assign('sidebarCategory', $this->renderAction(array('sidebarSpecialType', 'SpecialNewsCP', 'NewsCP')));
		
			$this->tpl->assign('listLink', $this->url->action('index', array('catid' => @$this->params['catid'],'type'=>$this->params['type'])));
		}
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view($news);
	}
	
	public function editPost(Models_NewsSpecial $model)
	{
        $model->content = base64_encode($model->content);
        $model->brief = base64_encode($model->brief);
		if($this->model->Update())
		{
			$this->url->redirectAction('index',array('catid'=>$model->category_id,'type'=>$model->special_type));
		}
		else 
		{
			$msg = "Can not update data: ".$model->db->error;
		}		
	}
	
	/**
	 * Xóa tin sắp xếp
	 */
	function deleteAjax()
	{
		$ids = $this->params['id'];
        $model = new Models_NewsSpecial();
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->db->where('id', $id)->Delete())
					return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != '')
		{
			if(!$model->db->where('id', $ids)->Delete())
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
        if(!empty($this->params['type']))
            Models_NewsSpecial::reOrder($this->params['type']);
		return json_encode(array('success' => true, 'link' => $this->url->action('index'), 'dataTable' => 'tableSpecialNews'));
	}

    /**
	 * Movedown news
	 */
	public function moveDownAjax()
	{
		if (!empty($this->params['id'])) {
			$modelNewsS = new Models_NewsSpecial();
			$news = $modelNewsS->db->select('id, orderno, special_type, category_id')->where('id',$this->params['id'])->getFields();
			//update orderno
			$sql = "update news_special set orderno=" . $news['orderno'] . " 
					where special_type='".$news['special_type']."'
							and orderno=" . ((int)$news['orderno'] + 1);
			if($modelNewsS->db->Execute($sql))
				if($modelNewsS->db->where('id',$news['id'])->update(array('orderno' => ((int)$news['orderno'] + 1))))
					return json_encode(array('success' => true));
			return json_encode(array('success' => false, 'msg' => $modelNewsS->error));
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa đúng'));
	}
	
	/**
	 * MoveUp news
	 */
	public function moveUpAjax()
	{
		if (!empty($this->params['id'])) {
			$modelNewsS = new Models_NewsSpecial();
			$news = $modelNewsS->db->select('id, orderno, special_type, category_id')->where('id',$this->params['id'])->getFields();
			//update orderno
			$sql = "update news_special set orderno=" . $news['orderno'] . " 
					where special_type='".$news['special_type']."'
							and orderno=" . ((int)$news['orderno'] - 1);
			if($modelNewsS->db->Execute($sql))
				if($modelNewsS->db->where('id',$news['id'])->update(array('orderno' => ((int)$news['orderno'] - 1))))
					return json_encode(array('success' => true));
			return json_encode(array('success' => false, 'msg' => $modelNewsS->error));			
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa đúng'));
	}
	
	/**
	 * get button special news
	 */
	function getButtons($sNews)
	{
		/*$maxNo = $this->db->GetFieldValue("select max(orderno) as maxValue from news_special 
				where special_type='".$sNews['special_type']."' and category_id='".$sNews['category_id']."'");*/
		$model = new Models_NewsSpecial();
		$minNo = $model->db->select('min(orderno)')->where('special_type',$sNews['special_type'])
            //->where('category_id',$sNews['category_id'])
            ->getField();
		$maxNo = $model->db->select('max(orderno)')->where('special_type',$sNews['special_type'])
            //->where('category_id',$sNews['category_id'])
            ->getField();
		$buttons = array();
		if ($sNews['orderno'] == $minNo)
		{
			$buttons[] = array(
					'onclick' => 'moveDownSNews(this)',
					'id' => $sNews['id'],
					'title' => 'MoveDown',
                    'class' => 'icon-circle-arrow-down',
                    'href' => 'javascript:'
			);
		}
		elseif ($sNews['orderno'] == $maxNo)
		{
			$buttons[] = array(
					'onclick' => 'moveUpSNews(this)',
					'id' => $sNews['id'],
					'title' => 'MoveUp',
                    'class' => 'icon-circle-arrow-up',
                    'href' => 'javascript:'
			);
		}
		else
		{
			$buttons[] = array(
					'onclick' => 'moveDownSNews(this)',
					'id' => $sNews['id'],
					'title' => 'MoveDown',
                    'class' => 'icon-circle-arrow-down',
                    'href' => 'javascript:'
			);
			$buttons[] = array(
					'onclick' => 'moveUpSNews(this)',
					'id' => $sNews['id'],
					'title' => 'MoveUp',
                    'class' => 'icon-circle-arrow-up',
                    'href' => 'javascript:'
			);
		}
		$buttons[] = array(
				'href' => $this->url->action('edit', array('id' => $sNews['id'],'type'=>@$this->params['type'])),
				'title' => 'Sửa',
                'class' => 'icon-edit'
		);
        $buttons[] = array(
            'href' => $this->url->action('delete', array('id' => $sNews['id'],'type'=>@$this->params['type'])),
            'title' => 'Xóa',
            'id' => $sNews['id'],
            'class' => 'icon-trash frm-delete-btn'
        );
		return $buttons;
	}

    function getHtmlBtnAction()
    {
        $this->unloadLayout();
        $buttons = $this->getButtons($this->params['snews']);
        foreach ($buttons as $key => $button)
        {
            $this->tpl->insert_loop('main.button', 'button', $button);
        }
        return $this->view();
    }
	
	/**
	 * Lấy tin đã chọn
	 */
	public function getSelectedNewsAjax()
	{
		if (!empty($_SESSION['sys_selectedNews']))
		{
			//Thực hiện thêm mới tin sắp xếp
			foreach ($_SESSION['sys_selectedNews'] as $news)
			{
				//Add to news_special	
				//$catId = (!empty($this->params['catid']))? $this->params['catid']: 0;
				$typeId = (!empty($this->params['typeid']))? $this->params['typeid']: 0;
				$data = array(
						'news_id' => $news['id'],
						'title' => $news['title'],
						'url_title' => $news['url_title'],
						'brief' => $news['brief'],
						'image_path' => $news['image_path'],
						'category_id' => $news['category_id'],
						'orderno' => 1,
						'special_type' =>$typeId,
						'lang_code' => @$_SESSION['sys_langcode']
				);
                $this->specialCatNews($news['category_id']);
				if(!$this->addSpecialNews($data))
					return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
			}
			unset($_SESSION['sys_selectedNews']);
			return json_encode(array('success' => true));
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa đúng'));
	}
    /*Đánh dấu danh mục tin có tin sắp xếp*/
    function specialCatNews($cat = 0)
    {
        if(!empty($cat))
        {
            $model = new Models_NewsCategory();
            $arrCat = $model->getCatParent($cat);
            $listCatId = '';
            if(!empty($arrCat))
                foreach($arrCat as $cat)
                    $listCatId .= $cat['id'].',';
            if(!empty($listCatId))
                $model->db->where_in('id',substr($listCatId,0,-1))->update(array('has_special'=>1));
        }
    }
    /**
     * Thêm mới tin sắp xếp vào danh mục
     */
    public function addSpecialCatAjax()
    {
        if (!empty($this->params['listId']))
        {
            $model = new Models_News();
            //Lưu danh sách id news vào session
            $listId = 'id='.str_replace(',', ' or id=', $this->params['listId']);
            $newses = $model->db->select('id,title,lang_code,url_title,brief,image_path,category_id,published_date')
                ->where($listId)->getFieldsArray();

            //Đưa vào tin sắp xếp
            foreach($newses as $n)
            {
                $data = array(
                    'news_id' => $n['id'],
                    'title' => $n['title'],
                    'url_title' => $n['url_title'],
                    'brief' => $n['brief'],
                    'image_path' => $n['image_path'],
                    'category_id' => $n['category_id'],
                    'published_date' => $n['published_date'],
                    'orderno' => 1,
                    'special_type' => 0,
                    'lang_code' => $n['lang_code']
                );
                if(!$this->addSpecialNews($data))
                    return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
            }

            return json_encode(array('success' => true, 'msg' => 'Cập nhật tin sắp xếp thành công'));
        }
        return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa chính xác'));
    }

    /**
	 * Thêm mới tin sắp xếp
	 */
	protected function addSpecialNews($data)
	{
		//Kiểm tra kiểu tin, danh mục, sắp xếp lại thứ tự
		if(!empty($data['special_type']))
		{
			$sql = "update news_special set orderno=orderno+1 where special_type='".$data['special_type']."' ";
		}
        elseif(!empty($data['category_id']))
		{
			$sql = "update news_special set orderno=orderno+1 where category_id='".$data['category_id']."'";
            //Update status has_special category to 1
            $model = new Models_NewsCategory();
            $model->db->where('id', $data['category_id'])->update(array('has_special' =>  1));
		}
		$modelNewsS = new Models_NewsSpecial();
		$modelNewsS->db->Execute($sql);
		return $modelNewsS->Insert($data);
	}	
	/**
	 * sidebar news category
	 */
	public function sidebarSpecialTypeAction()
	{
		$model = new Models_NewsSpecialType();
		//Lấy các loại tin sắp xếp
		/*$arrType = $this->db->getcFieldsArray("select * from news_special_type 
				where code <> 'category' and lang_code='".$_SESSION['sys_langcode']."'");*/
        if(!empty($_SESSION['sys_langcode']))
            $model->db->where('lang_code',$_SESSION['sys_langcode']);
		$arrType = $model->db->where('code <>','category')->orderby('title')->getFieldsArray();
		foreach ($arrType as $type)
		{
			$this->tpl->insert_loop('main.type', 'type', $type);
		}		
		//Danh mục tin
		$cats = $this->getCatsMultiLevel(0, @$_SESSION['sys_langcode']);
        if($cats)
        {
            foreach ($cats as $cat)
            {
                if (!empty($cat['subs']))
                {
                    foreach ($cat['subs'] as $sub)
                    {
                        $this->tpl->insert_loop('main.cat.hasSub.sub', 'sub', $sub);
                    }
                    $this->tpl->parse('main.cat.hasSub');
                }
                $this->tpl->insert_loop('main.cat', 'cat', $cat);
            }
        }
	
		$this->tpl->assign('listLink', $this->url->action('index'));
	
		$this->unloadLayout();
		return $this->view();
	}
	/**
	 * Lấy danh sách nhóm nhóm tin
	 */
	function getTreeCategory($parentId=0, $default = true, $langId='vi-VN')
	{
		$this->_listCat = array();
		if ($default)
			$this->_listCat['0'] = 'Danh mục gốc';
		return $this->_getTreeCategory($parentId,'', $langId);
	}
	
	private $_listCat = array();
	
	private function _getTreeCategory($parentId = 0, $prefix = '', $langId = 'vi-VN')
	{
		$model = new Models_NewsCategory();
		$cats = $model->db->select('id,title')->where('parent_id', $parentId)->getAll();
		//Lấy danh sách danh mục
		if( !empty($cats))
		{
			foreach ($cats as $key => $cat)
			{
				$this->_listCat[$cat['id']] = $prefix . $cat['title'];
				$this->_getTreeCategory($cat['id'], $prefix . '----', $langId);
			}
		}
		return $this->_listCat;
	}
	/**
	 * Lấy danh sách danh mục tin
	 */
	public function getCatsMultiLevel($parentId = 0, $langCode = 'vi-VN')
	{
		return $this->_getCatsMultiLevel($parentId, $langCode);
	}
	
	private function _getCatsMultiLevel($parentId = 0, $langCode = 'vi-VN')
	{
		$model = new Models_NewsCategory();
        if($langCode)
            $model->db->where('lang_code', $langCode);
		$cats = $model->db->select('id, title, parent_id')->where('parent_id',$parentId)
                    ->where('has_special', 1)->getFieldsArray();
		if (!empty($cats))
		{
			foreach ($cats as $key => $cat)
			{
				$cats[$key]['link'] = $this->url->action('index', array('catid' => $cat['id']));
				$childs = $this->_getCatsMultiLevel($cat['id'], $langCode);
				if ($childs)
					$cats[$key]['subs'] = $childs;
			}
			return $cats;
		}
		else
			return false;
	}	
}







