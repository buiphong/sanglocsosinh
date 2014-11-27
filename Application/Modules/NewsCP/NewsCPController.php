<?php
class NewsCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
        $this->loadModule(array('UsersCP'));
	}
	
	/**
	 * Hiển thị danh sách tin bài
	 */
	public function indexAction()
	{
		$pageSize = 10;
		
		$page = @$this->params['page'];
		if (empty($page))
			$page = 1;
		
		$offset = ($page - 1) * $pageSize;
		
		$model = new Models_News();
		$modelCat = new Models_NewsCategory();
		if (!isset($this->params['status']))
			$status = 0;
		else
			$status = $this->params['status'];
		
		if (empty($status))
			$status = 0;

		if (!empty($this->params['catid']))
		{
			$catId = $this->params['catid'];
			$this->tpl->assign('catName', '- ' . $modelCat->db->select('name')->where('id',$catId)->getOne());
		}
		else
			$catId = 0;
		
		//Danh mục tin
		$this->tpl->assign('sidebarCategory', $this->html->renderAction('sidebarNewsCategory', array('id' => @$catId)));		
		
		$model->db->select('news.id,news.title,news.created_date,news.created_by,news.status,news_category.title as category')
							->join('news_category', 'news.category_id=news_category.id', 'left');

        //Get multi category
		if (!empty($catId))
        {
            $childs = $modelCat->getChildCat($catId);
            $where = '(news.category_id=' . $catId;
            if(!empty($childs))
            {
                foreach($childs as $child)
                {
                    $where .= ' or news.category_id=' . $child['id'];
                }
            }
            $where .= ')';
            $model->db->where($where);
        }
		if ($status != 'a')
			$model->db->where('news.status', $status);
		if (!empty($this->params['search-text']))
			$model->db->like('news.title', $this->params['search-text']);
			
		//Language
        if(isset($_SESSION['sys_langcode']))
		    $model->db->where('news.lang_code', $_SESSION['sys_langcode']);
		
		//Đếm tin bài
		$totalRows = $model->db->count();
		
		$arrNews = $model->db->orderby('news.created_date', 'desc')->limit($pageSize, $offset)->getAll();
		if ($arrNews === false)
			$this->showError('Mysql Error', $model->db->error);
		if(isset($_SESSION['sys_selectedNews']))
        {
            foreach ($_SESSION['sys_selectedNews'] as $new)
                $idSelect[] = $new['id'];
        }
		if (!empty($arrNews))
		{
			$modelUser = new Models_User();
			foreach ($arrNews as $news)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $news['id'])));
				$buttons = $this->getNewsButtons($news);
				foreach ($buttons as $key => $button)
				{
					if ($key == 'edit')
					{
						$button['href'] = 'href="'.$this->url->action('edit', 
								array('catid' => @$this->params['catid'], 'id' => $button['id'])).'"';
					}
					elseif( !empty($button['href']))
						$button['href'] = 'href="'.$button['href'].'"';
                    else
                        $button['href'] = '';
					$this->tpl->insert_loop('main.news.button', 'button', $button);
				}
				
				//Get create user
				$news['created_by'] = $modelUser->db->select('fullname')->where('id', $news['created_by'])->getOne();
				
				$news['created_date'] = date('H:i d/m/Y', strtotime($news['created_date']));
				
				if(!empty($idSelect) && in_array($news['id'], $idSelect))
				{
					$news['selected'] = "selected";
					$news['checked'] = "checked ='checked'";
				}
				$news['commentLink'] = $this->url->action('comment','NewsCommentCP','NewsCP',array('newsId'=>$news['id']));
				$this->tpl->insert_loop('main.news', 'news', $news);
			}			
			$this->tpl->parse('main.button');
		}
		
		$newsStatus = $this->getArrRadioStatus();
		foreach ($newsStatus as $key => $s)
		{
			if ($key == $status)
				$s['checked'] = 'checked';
			else
				$s['checked'] = '';
			
			$s['key'] = $key;
			$this->tpl->insert_loop('main.status', 'status', $s);
		}
		
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		
		$this->tpl->assign('frmSearchAction', $this->url->action('index'));
		$this->tpl->assign('createLink', $this->url->action('create', array('catid' => @$catId)));
		$this->tpl->assign('textSearch', @$this->params['search-text']);
		$this->tpl->assign('addLink', $this->url->action('create', $this->params));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('catid', $catId);
		
		$this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		
		$this->setTitle('Quản lý tin bài');
		
		return $this->view();
	}
	
	public function indexPost()
	{
		$this->url->redirectAction('index', $this->params);
	}
	
	/**
	 * Danh sách tin bài
	 */
	public function listAjax()
	{
        $model = new Models_News();
        $modelCat = new Models_NewsCategory();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        if(!empty($this->params['sSearch']))
            $model->db->like('news.title', $this->params['sSearch']);
        if (isset($this->params['status']) && $this->params['status'] != 'a')
            $model->db->where('news.status', $this->params['status']);
        $catId = @$this->params['category_id'];
        //Get multi category
        if (!empty($catId))
        {
            $childs = $modelCat->getChildCat($catId);
            $where = '(news.category_id=' . $catId;
            if(!empty($childs))
            {
                foreach($childs as $child)
                {
                    $where .= ' or news.category_id=' . $child['id'];
                }
            }
            $where .= ')';
            $model->db->where($where);
        }
        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('created_date', 'desc');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('news.id,news.title,news.created_date,news.created_by,news.status,news_category.title as category,users.fullname,users.username')
            ->join('news_category', 'news.category_id=news_category.id', 'left')
            ->join('users', 'users.id=news.created_by', 'left')->orderby('news.created_date', 'desc')
            ->limit($pageSize,$offset)->getFieldsArray();
        if(!empty($datas))
        {
            foreach($datas as $key => $val)
            {
                $datas[$key]['title'] = "<a href=''>".$val['title']."</a>";
                if(!empty($val['created_date']))
                    $datas[$key]['created_date'] = date('d/m/Y H:i',strtotime($val['created_date']));
                $datas[$key]['btn'] = $this->html->renderAction('getHtmlNewsBtn', array('news' => $val));
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
	}
	
	/**
	 * Thêm mới tin bài
	 */
	public function createAction()
	{
		$this->setView('edit');
		//Lấy danh sách danh mục
		$this->tpl->assign('categoryId', 
				$this->html->genSelect('category_id', $this->getTreeCategory(), (int)@$this->params['catid'],
						'', '', array('style' => 'width: 307px;', 'class' => 'field')));

        $this->tpl->assign('other_category', $this->html->genSelect('other_categories[]', $this->getTreeCategory(0, false), (int)@$this->params['catid'],
            '', '', array('style' => 'width: 307px;', 'class' => 'chosen span6', 'multiple' => "multiple")));

        if(Helper::moduleExist("NewsTopicCP"))
        {
            $this->loadModule('NewsTopicCP');
            $modelNewsTopic = new Models_NewsTopic();
            $listTopic = $modelNewsTopic->db->select("id, name")->getFieldsArray();
            foreach($listTopic as $k => $v)
                $this->tpl->insert_loop("main.isNewsTopic.newsTopic", "newsTopic", $v);
            $this->tpl->parse("main.isNewsTopic");
        }
		$this->tpl->assign('sidebarCategory', $this->renderAction(array('sidebarNewsCategory', 'NewsCP', 'NewsCP')));
		$this->tpl->assign('form_action', $this->url->action('create'));
		$this->tpl->assign('listLink', $this->url->action('index', array('catid' => @$this->params['catid'])));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view();
	}
	
	public function createPost(Models_News $model)
	{
		$model->lang_code = @$_SESSION['sys_langcode'];
		$model->status = -1;
		$model->created_date = date('Y-m-d H:i');
		$model->created_by = $_SESSION["pt_control_panel"]["system_userid"];
		$model->content = base64_encode($model->content);
		$model->brief = base64_encode(strip_tags($model->brief));
        if($model->other_cateogories)
            $model->other_cateogories = implode(',',$model->other_cateogories);
		if($model->Insert())
		{
            if(!empty($this->params["news_topic"]))
            {
                $this->loadModule('NewsTopicCP');
                $modelTopicList = new Models_NewsTopicList();
                $newsTopic = $this->params["news_topic"];
                $topic = "";
                $topic["news_id"] = $model->db->InsertId();
                $topic["created_date"] = date("Y-m-d");
                $topic["created_uid"] = $_SESSION["vc_control_panel"]["system_userid"];
                if(is_array($newsTopic))
                {
                    foreach($newsTopic as $k => $v)
                    {
                        $topic["topic_id"] = $v;
                        if(!$modelTopicList->Insert($topic))
                            $this->showError("Query", $modelTopicList->db->error);
                    }
                }
            }

            //Tạo phiên bản tin tức
            $model->createVersion();
			$this->url->redirectAction('index', array('catid' => $model->category_id));
		}
		else
		{
			$this->showError('Query Error', $model->db->error);
		}
	}
	
	/**
	 * Sửa tin bài
	 */
	public function editAction()
	{
		if (!empty($this->params['id']))
		{			
			$model = new Models_News($this->params['id']);
			if(base64_decode($model->content, true))
				$model->content = base64_decode($model->content);
			if(base64_decode($model->brief, true))
				$model->brief = base64_decode($model->brief);
			//Lấy danh sách danh mục
			$this->tpl->assign('categoryId',
					$this->html->genSelect('category_id', $this->getTreeCategory(), $model->category_id,
							'', '', array('style' => 'width: 307px;', 'class' => 'field')));

            $this->tpl->assign('other_category', $this->html->genSelect('other_categories[]', $this->getTreeCategory(0, false), $model->other_categories,
                '', '', array('style' => 'width: 307px;', 'class' => 'chosen span6', 'multiple' => "multiple")));
			
			$this->tpl->assign('sidebarCategory', $this->renderAction(array('sidebarNewsCategory', 'NewsCP', 'NewsCP')));

			$this->tpl->assign('listLink', $this->url->action('index', array('catid' => @$this->params['catid'])));
            $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));

            if(Helper::moduleExist("NewsTopicCP"))
            {
                $this->loadModule('NewsTopicCP');
                $modelNewsTopic = new Models_NewsTopic();
                $modelListNewsTopic = new Models_NewsTopicList();
                $listTopic = $modelNewsTopic->db->select("id, name")->getFieldsArray();
                $listNewsTopic = $modelListNewsTopic->db->select("topic_id")->where("news_id", $this->params["id"])->getcFieldArray();
                foreach($listTopic as $k => $v)
                {
                    if(!empty($listNewsTopic))
                        if(in_array($v["id"], $listNewsTopic))
                            $v["selected"] = "selected";
                    $this->tpl->insert_loop("main.isNewsTopic.newsTopic", "newsTopic", $v);
                }
                $this->tpl->parse("main.isNewsTopic");
            }
		}		
		return $this->view($model);	
	}
	
	public function editPost(Models_News $model)
	{
		if (empty($model->published_date)) {
			$model->published_date = null;
		}
		$model->lang_code = @$_SESSION['sys_langcode'];
		$model->content = base64_encode($model->content);
		$model->brief = base64_encode(strip_tags($model->brief));
        if($model->other_categories)
            $model->other_categories = implode(',',$model->other_categories);
		if ($model->Update())
		{
            //Tạo phiên bản tin tức
            $model->createVersion();
            if(Helper::moduleExist("NewsTopicCP"))
            {
                $this->loadModule('NewsTopicCP');
                if(!empty($this->params["news_topic"]))
                {
                    $topic = array("created_date" => date("Y-m-d"), "created_uid" => $_SESSION["pt_control_panel"]["system_userid"]);
                    $modelListTopic = new Models_NewsTopicList();
                    $news_topic = $this->params["news_topic"];
                    $listNewsTopic = $modelListTopic->db->select("id, topic_id")->where("news_id", $this->params["id"])->getcFieldsArray();
                    if(empty($listNewsTopic))
                    {
                        $topic["news_id"] = $this->params["id"];
                        foreach($news_topic as $k => $v)
                        {
                            $topic["topic_id"] = $v;
                            $modelListTopic->Insert($topic);
                        }
                    }
                    else
                    {
                        foreach($listNewsTopic as $k=>$v)
                        {
                            if(in_array($v['topic_id'], $news_topic))
                            {
                                $modelListTopic->db->where("id", $v["id"])->update($topic);
                                $key = array_search($v["topic_id"], $news_topic);
                                unset($news_topic[$key]);
                            }
                            else
                               $modelListTopic->Delete("id = ".$v['id']);
                        }
                        if(!empty($news_topic))
                        {
                            $topic["news_id"] = $this->params["id"];
                            foreach($news_topic as $value)
                            {
                                $topic["topic_id"] = $value;
                                $modelListTopic->Insert($topic);
                            }
                        }
                    }
                }
            }
			$this->url->redirectAction('index',array('catid'=>$model->category_id));
		}
		else 
		{
			$this->showError('Query Error', $model->error);
		}			
	}
	
	/**
	 * Xóa tin bài
	 */
	function deleteAjax()
	{
		$ids = $this->params['listid'];
		$model = new Models_News();
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                    if(!$model->Delete("id= $id"))
                        return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
	
	/**
	 * duyệt xuất bản
	 */
	public function publishNewsAjax()
	{
		$this->unloadLayout();
		$model = new Models_News();
		if (!empty($this->params['newsid']))
		{
			$news = $model->db->select('id,title,brief,content,image_path')
							->where('id', $this->params['newsid'])->getFields();
			if ($news)
			{
				if(base64_decode($news['brief'], true))
					$news['brief'] = base64_decode($news['brief']);
				if(base64_decode($news['content'], true))
					$news['content'] = base64_decode($news['content']);
				$this->tpl->assign('news', $news);
				return json_encode(array('success' => true, 'html' => $this->view()));
			}
			else
				return json_encode(array('success' => false, 'msg' => 'Không tìm thấy tin bài'));
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
	}
	
	public function publishNewsPostAjax()
	{
		if (!empty($this->params['newsid']))
		{
			$model = new Models_News($this->params['newsid']);
			$data = array('status' => 1);
			if (empty($model->published_date))
				$data['published_date'] = date('Y-m-d H:i:s');
			
			//update status news to 1
			if($model->db->where('id', $this->params['newsid'])->update($data))
			{
                $model->createVersion();
				return json_encode(array('success' => true));
			}
			else
				return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
		}
		else
		{
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
		}
	}
	
	public function getDownNewsAjax()
	{
		if (!empty($this->params['newsid']))
		{
            $model = new Models_News($this->params['newsid']);
			//update status news to 2
			if($model->db->where('id', $this->params['newsid'])->update(array('status' => 2)))
			{
                $model->createVersion();
				return json_encode(array('success' => true));
			}
			else
				return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
	}
	
	/**
	 * Lựa chọn tin bài
	 */
	public function selectNewsAjax()
	{
		$modelNews = new Models_News();
		if (!empty($this->params['listId']))
		{
			//Lưu danh sách id news vào session
			$listId = 'id='.str_replace(',', ' or id=', $this->params['listId']);
			$newses = $modelNews->db->select('id,title,url_title,brief,image_path,category_id')
							->where($listId)->getFieldsArray();
			
			$_SESSION['sys_selectedNews'] = $newses;

			return json_encode(array('success' => true, 'msg' => 'Chọn tin thành công'));
		}
		return json_encode(array('success' => false, 'msg' => 'Dữ liệu chưa chính xác'));
	}
	
	/**
	 * sidebar news category
	 */
	public function sidebarNewsCategoryAction()
	{
		$modelCat = new Models_NewsCategory();
		//Danh mục tin
		$cats = $modelCat->getListCatMultiLevel(0, @$_SESSION['sys_langcode']);
		if($cats)
            $this->tpl->assign('html', $this->html->renderAction('_childSidebarNewsCat', array('cats' => $cats, 'selected' => @$this->params['id'])));
		$this->tpl->assign('listLink', $this->url->action('index'));
		
		$this->unloadLayout();
		return $this->view();
	}

    public function _childSidebarNewsCatAction()
    {
        foreach ($this->params['cats'] as $cat)
        {
            if (!empty($cat['subs']))
            {
                $this->tpl->assign('child', $this->html->renderAction('_childSidebarNewsCat',
                    array('cats' => $cat['subs'], 'selected' => $this->params['selected'])));
            }
            else
                $this->tpl->assign('child', '');
            if ($cat['id'] == $this->params['selected']) {
                $cat['class'] = 'current';
            }
            else
                $cat['class'] = '';
            $cat['link'] = $this->url->action('index', array('catid'=>$cat['id']));
            //$this->tpl->assign('child', $this->html->renderAction('_childSidebarNews', array('cats' =>)));
            $this->tpl->insert_loop('main.cat', 'cat', $cat);
        }
        $this->unloadLayout();
        return $this->view();
    }
	
	/**
	 * Danh sách trạng thái tin bài - radio
	 */
	public function getArrRadioStatus()
	{
		$arr = array(
				-1 => array('class' => 'draft', 'title' => 'Bản nháp'),
				1 => array('class' => 'approved', 'title' => 'Đã duyệt'),
				2 => array('class' => 'gottendown', 'title' => 'Đã hạ xuống'),
				-2 => array('class' => 'deleted', 'title' => 'Đã hủy'),
				'a' => array('class' => 'deleted', 'title' => 'Toàn bộ'),
		);
		
		return $arr;
	}
	
	/**
	 * Get news button
	 */
	function getNewsButtons($news)
	{
		if (!is_array($news))
		{
			$model = new Models_News();
			$news = $model->db->select('id,title,status,created_by')->where('id', $news)->getFields();
		}
		$buttons = array();
		//Kiểm tra status, user
		if (($news['status'] == -1 || $news['status'] == 2))
		{
			//Cho phép duyệt, sửa, xóa
			if($this->isPublisher())
				$buttons['publish'] = array('title' => 'Duyệt', 'id' => $news['id'],
					'onclick' => 'publishNews(this)', 'class' => 'icon-ok');
			$buttons['edit'] = array('title' => 'Sửa', 'id' => $news['id'],
				'onclick' => 'editNews(this)', 'class' => 'icon-edit');
			$buttons['delete'] = array('title' => 'Xóa', 'id' => $news['id'],
				'onclick' => "CPConfirm('Bạn có chắc chắn muốn xoá không ?',deleteNews, ".$news['id'].");",
                'class' => 'icon-trash');
		}
		else 
		{
			//hạ tin bài
			if($this->isPublisher())
				$buttons['getDown'] = array('title' => 'Hạ tin bài', 'id' => $news['id'],
					'onclick' => 'getDownNews(this)', 'class' => 'icon-circle-arrow-down');
			else
				$buttons['getDown'] = array('title' => 'Đã đăng', 'id' => null, 'class'=>'icon-ok');
		}

        //News comment
		$newsCmt = new Models_NewsComment();
        if($newsCmt->countCmt($news['id']) > 0)
            $buttons['comment'] = array('title' => 'Comment', 'href' => $this->url->action('index', 'NewsCommentCP', array('newsId' => $news['id'])), 'class'=>'icon-comment');
        $buttons['relative'] = array('title' => 'Tin liên quan', 'id' => $news['id'], 'href' => '/NewsCP/NewsRelativeCP/index?newsId='.$news['id'], 'class' => 'icon-share');
		return $buttons;
	}

    function getHtmlNewsBtnAction()
    {
        $this->unloadLayout();
        $buttons = $this->getNewsButtons($this->params['news']);
        foreach ($buttons as $key => $button)
        {
            if ($key == 'edit')
            {
                $button['href'] = 'href="'.$this->url->action('edit',
                        array('catid' => @$this->params['catid'], 'id' => $button['id'])).'"';
            }
            elseif( !empty($button['href']))
                $button['href'] = 'href="'.$button['href'].'"';
            else
                $button['href'] = '';
            $this->tpl->insert_loop('main.button', 'button', $button);
        }
        return $this->view();
    }
	
	/**
	 * Danh mục khác
	 */
	public function otherCategoryAction()
	{
		$arrNewsCat = $this->getListCatMultiLevel();
		//$catCP = new CategoryCP();
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
        if(isset($_SESSION['sys_langcode']))
            $model->db->where('lang_code', $_SESSION['sys_langcode']);
		$cats = $model->db->select('id,title')->where('parent_id', $parentId)->getcFieldsArray();
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
	 * Check publish news permission
	 * @return boolean
	 */
	private function isPublisher(){
		$router = new Router();
		$router->module = 'NewsCP';
		$router->controller = 'NewsCP';
		$router->action = 'publishNews';
		//Check user permission
		$hasPermission = $this->renderAction(array('checkPermission', 'Permission', 'Permission', array('router' => $router)));
		return $hasPermission;
	}
}

