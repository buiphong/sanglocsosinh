<?php
class DocumentsCPController extends Controller{
	
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Hiển thị danh sách thư viện, tài liệu
	 */
	public function indexAction(){

		$modelCat = new Models_DocumentsCategory();

		if (!empty($this->params['catid'])){
			$catId = $this->params['catid'];
			$this->tpl->assign('catName', '- ' . $modelCat->db->select('name')->where('id',$catId)->getOne());
		}
		else
			$catId = 0;
		
		//Danh mục tin
		$this->tpl->assign('sidebarCategory', $this->html->renderAction('sidebarDocumentsCategory', array('id' => @$catId)));

		$this->tpl->assign('createLink', $this->url->action('create', array('catid' => @$catId)));
		$this->tpl->assign('textSearch', @$this->params['search-text']);
		$this->tpl->assign('addLink', $this->url->action('create', $this->params));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('catid', $catId);
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
		$this->setTitle('Quản lý thư viện, tài liệu');

		return $this->view();
	}

    /**
     * Danh sách tài liệu
     */
    public function listAjax()
    {
        $model = new Models_Documents();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];
        $model->db->select('documents.id,documents.title,documents.created_time,documents_category.title as category,documents.status')
                ->join('documents_category', 'documents_category.id=documents.category', 'left');
        //Lấy theo từng cấp
        if (!empty($this->params['category']))
            $model->db->where('documents.category', $this->params['category']);

        if(!empty($this->params['sSearch']))
            $model->db->like('documents.title', $this->params['sSearch']);

        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('created_time', 'desc');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->limit($pageSize,$offset)->getFieldsArray();
        foreach($datas as $key => $val)
        {
            if(!empty($val['created_time']))
                $datas[$key]['created_time'] = date('d/m/Y H:i:s',strtotime($val['created_time']));
            $datas[$key]['btn'] = $this->html->renderAction('getHtmlDocBtn', array('doc' => $val));
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

	/**
	 * Thêm mới thư viện, tài liệu
	 */
	public function createAjax(){
		$this->setView('edit');
		//Lấy danh sách danh mục
		$this->tpl->assign('category', $this->html->genSelect('category', $this->getTreeCategory(), (int)@$this->params['catid'], '', '', array('style' => 'width: 307px;', 'class' => 'field')));
		
		$this->tpl->assign('sidebarCategory', $this->renderAction(array('sidebarDocumentsCategory', 'DocumentsCP','DocumentsCP')));
        $this->tpl->assign('form_action', $this->url->action('createPost'));
		$this->tpl->assign('listLink', $this->url->action('index', array('catid' => @$this->params['catid'])));
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        if(Helper::moduleExist('MembersCP'))
            $this->tpl->parse('main.member');
        $this->unloadLayout();
		return $this->view();
	}
	
	public function createPostAjax(Models_Documents $model){
        if(!empty($_SESSION['sys_langcode']))
		    $model->lang_code = $_SESSION['sys_langcode'];
		$model->status = -1;
		$model->created_time = date('Y-m-d H:i:s');
		$model->content = base64_encode($model->content);
        if(!empty($model->file_url))
        {
            //Get file info
            $path = pathinfo($model->file_url, PATHINFO_EXTENSION);
            $data['type'] = $path;
            $data['size'] = filesize(Url::getUrlContent($model->file_url));
            $model->file_info = serialize($data);
        }
		if($model->Insert()){
            return json_encode(array('success' => true, 'dataTable' => 'tableDocument'));
		}
		else{
            return json_encode(array('success' => false, 'msg' => $model->error));
		} 
	}
	
	/**
	 * Sửa thư viện, tài liệu
	 */
	public function editAjax(){
		if (!empty($this->params['id'])){			
			$model = new Models_Documents($this->params['id']);
			if(base64_decode($model->content, true))
				$model->content = base64_decode($model->content);
			if(base64_decode($model->description, true))
				$model->description = base64_decode($model->description);
			//Lấy danh sách danh mục
			$this->tpl->assign('category', $this->html->genSelect('category', $this->getTreeCategory(), $model->category, '', '', array('style' => 'width: 307px;', 'class' => 'field')));
			
			$this->tpl->assign('sidebarCategory', $this->renderAction(array('sidebarDocumentsCategory', 'DocumentsCP','DocumentsCP')));
			
			$this->tpl->assign('listLink', $this->url->action('index', array('catid' => @$this->params['catid'])));			
		}
        $this->tpl->assign('rightHeader', $this->html->renderAction('headerNotifyIcon', 'ComponentCP', 'ControlPanel'));
        $this->tpl->assign('form_action', $this->url->action('editPost'));
        if(Helper::moduleExist('MembersCP'))
        {
            if($model->member == 1)
                $this->tpl->assign('checked', 'checked');
            else
                $this->tpl->assign('checked', 'checked');
            $this->tpl->parse('main.member');
        }
        $this->unloadLayout();
		return $this->view($model);	
	}
	
	public function editPostAjax(Models_Documents $model){
        if(!empty($_SESSION['sys_langcode']))
		    $model->lang_code = $_SESSION['sys_langcode'];
		$model->content = base64_encode($model->content);
        if(!isset($this->params['member']))
            $model->member = 0;
        if(!empty($model->file_url))
        {
            //Get file info
            $path = pathinfo($model->file_url, PATHINFO_EXTENSION);
            $data['type'] = $path;
            $data['size'] = filesize(Url::getAppDir().$model->file_url);
            $model->file_info = serialize($data);
        }
		if ($model->Update()){
			return json_encode(array('success' => true, 'dataTable' => 'tableDocument'));
		}
		else {
            return json_encode(array('success' => false, 'msg' => $model->error));
		}			
	}
	
	/**
	 * Xóa thư viện, tài liệu
	 */
	function deleteAjax(){
		$ids = $this->params['id'];
		$model = new Models_Documents();
		if (strpos($ids, ',') !== false){
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				    if(!$model->Delete("id= $id"))
					    return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != ''){
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'dataTable' => 'tableDocument', 'link' => $this->url->action('index')));
	}
	
	/**
	 * duyệt xuất bản
	 */
	public function publishDocumentsAjax(){
		$this->unloadLayout();
		$model = new Models_Documents();
		if (!empty($this->params['documentsid'])){
			$documents = $model->db->select('id,title,content,file_url')->where('id', $this->params['documentsid'])
                ->getFields();
			if ($documents){
				if(base64_decode($documents['content'], true))
					$documents['content'] = base64_decode($documents['content']);
				$this->tpl->assign('documents', $documents);
				$status = @$this->params['view'];
				if($status != 1)
				{
					$this->tpl->parse("main.btApprove");
				}
				return json_encode(array('success' => true,'title'=>$documents['title'], 'html' => $this->view()));
			}
			else
				return json_encode(array('success' => false, 'msg' => 'Không tìm thấy thư viện, tài liệu'));
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
	}
	
	public function publishDocumentsPostAjax(){
		if (!empty($this->params['documentsid'])){
			$model = new Models_Documents();
			$data = array('status' => 1, 'published_time' => date('Y-m-d H:i:s'));
			//update status documents to 1
			if($model->db->where('id', $this->params['documentsid'])->update($data)){
				return json_encode(array('success' => true));
			}
			else
				return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
		}
		else{
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
		}
	}
	
	public function getDownDocumentsAjax(){
		$model = new Models_Documents();
		if (!empty($this->params['documentsid'])){
			//update status documents to 2
			if($model->db->where('id', $this->params['documentsid'])->update(array('status' => 2))){
				return json_encode(array('success' => true));
			}
			else
				return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
	}
	
	/**
	 * Lựa chọn thư viện, tài liệu
	 */
	public function selectDocumentsAjax(){
		$modelDocuments = new Models_Documents();
		if (!empty($this->params['listId'])){
			//Lưu danh sách id documents vào session
			$listId = 'id='.str_replace(',', ' or id=', $this->params['listId']);
			$documents = $modelDocuments->db->select('id,name,url_title,brief,image_path')->where($listId)->getFieldsArray();
			
			$_SESSION['sys_selectedDocuments'] = $documents;

			return json_encode(array('success' => true, 'msg' => 'Chọn tin thành công'));
		}
		return json_encode(array('success' => false, 'msg' => 'Dữ liệu chưa chính xác'));
	}
	
	/**
	 * sidebar documents category
	 */
	public function sidebarDocumentsCategoryAction(){
		$selected = @$this->params['id'];
		$modelCat = new Models_DocumentsCategory();
		//Danh mục tin
		$cats = $modelCat->getListCatMultiLevel(0, @$_SESSION['sys_langcode']);
        if($cats)
        {
            foreach ($cats as $cat){
                if (!empty($cat['subs'])){
                    foreach ($cat['subs'] as $sub){
                        if ($sub['id'] == $selected) {
                            $sub['class'] = 'current';
                        }
                        else
                            $sub['class'] = '';
                        $sub['link'] = $this->url->action('index', array('catid'=>$sub['id']));
                        $this->tpl->insert_loop('main.cat.child.sub', 'sub', $sub);
                    }
                    $this->tpl->parse('main.cat.child');
                }
                if ($cat['id'] == $selected) {
                    $cat['class'] = 'current';
                }
                else
                    $cat['class'] = '';
                $cat['link'] = $this->url->action('index', array('catid'=>$cat['id']));
                $this->tpl->insert_loop('main.cat', 'cat', $cat);
            }
        }
		$this->tpl->assign('listLink', $this->url->action('index'));
		
		$this->unloadLayout();
		return $this->view();
	}
	
	/**
	 * Danh sách trạng thái thư viện, tài liệu
	 */
	public function getArrRadioStatus(){
		$arr = array(
				-1 => array('class' => 'draft', 'title' => 'Bản nháp'),
				1 => array('class' => 'approved', 'title' => 'Đã duyệt'),
				2 => array('class' => 'gottendown', 'title' => 'Đã hạ xuống'),
				-2 => array('class' => 'deleted', 'title' => 'Đã hủy'),
				'a' => array('class' => 'deleted', 'title' => 'Toàn bộ'),
		);
		
		return $arr;
	}

    function getHtmlDocBtnAction()
    {
        $this->unloadLayout();
        $buttons = $this->getDocumentsButtons($this->params['doc']);
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
	 * Get documents button
	 */
	function getDocumentsButtons($documents){
		if (!is_array($documents)){
			$model = new Models_Documents();
			$documents = $model->db->select('id,name,status,created_by')->where('id', $documents)->getFields();
		}
		$buttons = array();
		//Kiểm tra status, user
		if (($documents['status'] == -1 || $documents['status'] == 2)){
			//Cho phép duyệt, sửa, xóa
			$buttons['publish'] = array('title' => 'Duyệt', 'id' => $documents['id'], 'onclick' => 'publishDocuments(this)', 'class' => 'icon-ok');
			$buttons['edit'] = array('title' => 'Sửa thông tin tài liệu', 'id' => $documents['id'], 'class' => 'icon-edit frm-edit-btn-ajax');
			$buttons['delete'] = array('title' => 'Xóa', 'id' => $documents['id'], 'href' => $this->url->action('delete', array('id'=> $documents['id'])), 'class' => 'frm-delete-btn icon-trash');
		}
		else {
			//hạ thư viện, tài liệu
			$buttons['getDown'] = array('title' => 'Hạ thư viện, tài liệu', 'id' => $documents['id'], 'onclick' => 'getDownDocuments(this)', 'class' => 'icon-circle-arrow-down');
		}
		
		return $buttons;
	}
	
	/**
	 * Danh mục khác
	 */
	public function otherCategoryAction(){
		$arrDocumentsCat = $this->getListCatMultiLevel();
		//$catCP = new CategoryCP();
	}
	/**
	 * Lấy danh sách nhóm nhóm tin
	 */
	function getTreeCategory($parentId=0, $default = true, $langId='vi-VN'){
		$this->_listCat = array();
		if ($default)
			$this->_listCat['0'] = 'Danh mục gốc';
		return $this->_getTreeCategory($parentId,'', $langId);
	}
	
	private $_listCat = array();
	
	private function _getTreeCategory($parentId = 0, $prefix = '', $langId = 'vi-VN'){
		$model = new Models_DocumentsCategory();
        if(!empty($_SESSION['sys_langcode']))
            $model->db->where('lang_code', $_SESSION['sys_langcode']);
		$cats = $model->db->select('id,title')->where('parent_id', $parentId)->getAll();
		//Lấy danh sách danh mục
		if( !empty($cats)){
			foreach ($cats as $key => $cat){
				$this->_listCat[$cat['id']] = $prefix . $cat['title'];
				$this->_getTreeCategory($cat['id'], $prefix . '----', $langId);
			}
		}
		return $this->_listCat;
	}
}