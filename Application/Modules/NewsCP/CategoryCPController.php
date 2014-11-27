<?php
class CategoryCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}

    function member()
    {
        $model = new Models_MemberType();
        $arrMembers = $model->db->getFieldsArray();
        array_unshift($arrMembers,array('id'=>0,'name'=>'Là thành viên'));
        array_unshift($arrMembers,array('id'=>-1,'name'=>'Không yêu cầu đăng nhập'));
        return $arrMembers;
    }
	/**
	 * Danh sách danh mục tin
	 */
	public function indexAction()
	{
		if (!empty($this->params['parentid']))
		{
            $model = new Models_NewsCategory();
			$parentid = $this->params['parentid'];
			$parentName = $model->db->select('name')->where('id', $parentid)->getField();
		}
		else
		{
			$parentid = 0;
			$parentName = 'Danh mục cha';
		}
        $this->tpl->assign('parentid', $parentid);
		$this->tpl->assign('createLink', $this->url->action('create', array('parentid' => @$this->params['parentid'])));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action('index'));
		$this->tpl->assign('parentName', $parentName);
		$this->tpl->assign('breadCrumb', $this->html->renderAction('getBreadCrumb', array('parentid' => $parentid)));
		$this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}

    /**
     * Danh sách danh mục tin
     */
    public function listAjax()
    {
        $model = new Models_NewsCategory();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        //Lấy theo từng cấp
        if (!empty($this->params['parentid']))
        {
            $parentid = $this->params['parentid'];
            $model->db->where('parent_id', $parentid);
        }

        if(!empty($this->params['sSearch']))
            $model->db->like('news_category.title', $this->params['sSearch']);

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
        $datas = $model->db->select('id,title,orderno,image_path')
            ->limit($pageSize,$offset)->getFieldsArray();
        if(!empty($datas))
        {
            foreach($datas as $key => $val)
            {
                $datas[$key]['title'] = "<a href='".$this->url->action('index', array('parentid' => $val['id']))."'>".$val['title']."</a>";
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
	
	/**
	 * Thêm mới danh mục
	 */
	public function createAjax()
	{
		$this->setView('edit');
		$model = new Models_NewsCategory();
		//Lấy danh sách danh mục để làm danh mục cha
		$arrCat = $model->getTreeCategory(0, true, @$_SESSION['sys_langcode']);
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $arrCat, @$this->params['parentid']));
		$this->tpl->assign('listLink', $this->url->action('index', array('parentid' => @$this->params['parentid'])));
		$this->tpl->assign('catName', 'Thêm mới');
        $max = Models_NewsCategory::getMaxOrderNo(@$this->params['parentid']);
        $max += 1;
        $this->tpl->assign('order_no', $this->html->getCbmOrder('orderno', $max, $max));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('form_action', $this->url->action('createPost'));
        $this->unloadLayout();
		return $this->view();
	}
	
	public function createPostAjax(Models_NewsCategory $model)
	{
        if(MULTI_LANGUAGE && !empty($_SESSION['sys_langcode']))
		    $model->lang_code = $_SESSION['sys_langcode'];
		if($model->Insert())
        {
            //Update path
            if(!empty($model->parent_id))
            {
                $path = $model->db->select('path')->where('id', $model->parent_id)->getField();
                $path .= '/' . $model->db->InsertId();
            }
            else
                $path = $model->id;
            $model->db->where('id', $model->db->InsertId())->update(array('path' => $path));
            return json_encode(array('success' => true, 'dataTable' => 'tableNewsCat',
                'msg' => 'Thêm danh mục tin thành công'));
        }
		else
            return json_encode(array('success' => false, 'msg' => $this->model->error));
	}
	
	/**
	 * Sửa thông tin danh mục
	 */
	public function editAjax()
	{
		$model = new Models_NewsCategory($this->params['id']);
		$this->tpl->assign('parent_id', $this->html->genSelect('parent_id', $model->getTreeCategory(0, true, @$_SESSION['sys_langcode']), $model->parent_id));
		$this->tpl->assign('listLink', $this->url->action('index', array('parentid' => $model->parent_id)));
		if ($model->has_rss)
			$this->tpl->assign('checked', 'checked');
		else
			$this->tpl->assign('checked', '');
		
		if ($model->is_member)
			$this->tpl->assign('isMember', 'checked');
		else
			$this->tpl->assign('isMember', '');
		
		$this->tpl->assign('catName', $model->title);

        $max = Models_NewsCategory::getMaxOrderNo($model->parent_id);
        $this->tpl->assign('order_no', $this->html->getCbmOrder('orderno', $max, $model->orderno));
        $this->tpl->assign('form_action', $this->url->action('editPost'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->unloadLayout();
		return $this->view($model);
	}
	
	public function editPostAjax(Models_NewsCategory $model)
	{
        //Check for order_no
        if($model->orderno != $this->params['old_orderno'])
            $model->db->where('orderno', $model->orderno)->where('parent_id', $model->parent_id)
                        ->update(array('orderno' => $this->params['old_orderno']));

        if(MULTI_LANGUAGE && !empty($_SESSION['sys_langcode']))
		    $model->lang_code = $_SESSION['sys_langcode'];
		if($model->Update())
        {
            //Update path
            if(!empty($model->parent_id))
            {
                $path = $model->db->select('path')->where('id', $model->parent_id)->getField();
                $path .= '/' . $model->id;
            }
            else
                $path = $model->id;
            $model->db->where('id', $model->id)->update(array('path' => $path));
            return json_encode(array('success' => true,'dataTable' => 'tableNewsCat',
                'msg' => 'Cập nhật danh mục tin thành công'));
        }
		else
            return json_encode(array('success' => false, 'msg' => $this->model->error));
	}
	
	function deleteAjax()
	{
		$model = new Models_NewsCategory();
		$ids = $this->params['id'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
            {
                if ($id != '')
                {
                    if(!$model->deleteCat($id))
                        return json_encode(array('success' => false, 'msg' => $model->error));
                }
            }
		}
		elseif ($ids != '')
		{
			if(!$model->deleteCat($ids))
				return json_encode(array('success' => false, 'msg' => $$model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index'),'dataTable' => 'tableNewsCat'));
	}
	
	/**
	 * Lấy đường dẫn vị trí
	 */
	public function getBreadCrumbAction()
	{
		$model = new Models_NewsCategory();
		$this->unloadLayout();
		if (!empty($this->params['parentid'])) {
			//Check parent
			$arrParent = $model->getArrayParent($this->params['parentid']);
			foreach ($arrParent as $cat)
			{
				$this->tpl->assign('catLink', $this->url->action('index', array('parentid' => $cat['id'])));
				$this->tpl->insert_loop('main.cat', 'cat', $cat);
			}
		}
		return $this->view();
	}

    public function updatePathAction()
    {
        $model = new Models_NewsCategory();
        $cats = $model->db->select('id,title,parent_id,path')->orderby('parent_id')->getFieldsArray();
        foreach($cats as $item)
        {
            if(empty($item['path']))
                $item['path'] = $item['id'];
            $path = '';
            if(!empty($item['parent_id']))
                $path = $model->db->select('path')->where('id', $item['parent_id'])->getField();
            if($path && strpos($item['path'], $path) === false)
                $path .= '/' . $item['path'];
            else
                $path = $item['path'];
            $model->db->where('id', $item['id'])->update(array('path' => $path));
        }
        echo 'Done!';
    }
}