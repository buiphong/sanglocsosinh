<?php
class FaqCPController extends Controller
{
	public $arrStatus = array(0=>'Không hiển thị',1=>'Hiển thị');
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	public function indexAction()
	{
		$model = new Models_Faqs();
        $modelCat = new Models_FaqsCategory();
        if (!empty($this->params['catid']))
        {
            $catId = $this->params['catid'];
            $model->db->where('category_id',$catId);
            $this->tpl->assign('catid',$catId);
            $this->tpl->assign('catName', '- ' . $modelCat->db->select('name')->where('id',$catId)->getOne());
        }
        //Danh mục faq
        $this->tpl->assign('sidebarCategory', $this->html->renderAction('sidebarCategory', array('id' => @$catId)));
        $this->tpl->assign('createLink', $this->url->action('create'));
		return $this->view();
	}

    public function listAjax()
    {
        $model = new Models_Faqs();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];
        $cat = @$this->params['catid'];
        if(!empty($cat))
            $cat = Models_FaqsCategory::getById($cat);
        $model->db->join('faq_category', 'faq.category_id=faq_category.id', 'left');
        if(!empty($cat))
        {
            $model->db->where('faq.category_id', $cat['id']);
            $this->tpl->assign('catName', '- ' . $cat['title']);
        }

        if(!empty($this->params['sSearch']))
            $model->db->like('faq.question', $this->params['sSearch']);

        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('orderno', 'asc');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('faq.id,faq.question,faq.orderno,faq.category_id,faq.orderno,faq.hits,
            faq_category.name as category,faq.created_time,faq.fullname,faq.email,faq.status')
            ->limit($pageSize,$offset)->getFieldsArray();
        if(!empty($datas))
        {
            foreach($datas as $key => $val)
            {
                if($val['status'] == 1)
                    $val['status'] = 'Hiển thị';
                else
                    $val['status'] = 'Không hiển thị';
                $datas[$key]['btn'] = $this->html->renderAction('getHtmlBtn', array('faq' => $val));
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

	public function createAction()
	{
		$this->setView('edit');
        $modelCat = new Models_FaqsCategory();
        $model = new Models_Faqs();
        if(!empty($_SESSION['sys_langcode']))
            $modelCat->db->where('lang_code', $_SESSION['sys_langcode']);
        $cats = $modelCat->db->select('id,name')->orderby('orderno','asc')->getFieldsArray();
        $this->tpl->assign('category',$this->html->genSelect('category_id',$cats,@$this->params['catid'],'id','name'));
        $c = reset($cats);
        $maxNo = Models_Faqs::getMaxOrder($c['id']);
        $model->orderno = $maxNo + 1;
		$this->tpl->assign('rdStatus',$this->html->genRadio('status', $this->arrStatus));
		$this->tpl->assign('form_action',$this->url->action('create'));
        $this->tpl->assign('listLink',$this->url->action('index',array('catid' => @$this->params['catid'])));
		return $this->view($model);
	}
	
	public function createPost(Models_Faqs $model)
	{
		if(!empty($model->question))
		{
			$model->created_time = date('Y:m:d H:s:m');
            $model->fullname = $_SESSION['sys_fullname'];
			if($model->Insert())
			{
				$this->url->redirectAction('index');
			}
			else
			{
				$this->showError('Query Error', $model->db->error);
			}
		}
	}
	
	public function editAction()
	{
		if(!empty($this->params['id']))
		{
			$id = $this->params['id'];
			$model = new Models_Faqs($id);
            $modelCat = new Models_FaqsCategory();
            if(MULTI_LANGUAGE)
                $modelCat->db->where('lang_code', $_SESSION['sys_langcode']);
            $cats = $modelCat->db->select('id,name')->orderby('orderno','asc')->getFieldsArray();
            $this->tpl->assign('category',$this->html->genSelect('category_id',$cats,$model->category_id,'id','name'));

			$this->tpl->assign('rdStatus',$this->html->genRadio('status', $this->arrStatus, $model->status));
			$this->tpl->assign('form_action',$this->url->action('edit'));
            $this->tpl->assign('listLink',$this->url->action('index', array('catid' => $model->category_id)));
			return $this->view($model);
		}
	}
	
	public function editPost(Models_Faqs $model)
	{
		if(!empty($model->id))
		{
			if($model->Update())
			{
				$this->url->redirectAction('index');
			}
			else
			{
                $this->showError('Query Error', $model->db->error);
			}
		}
	}
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$model = new Models_Faqs();
		$ids = $this->params['id'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                    if(!$model->Delete("id=$id"))
                        return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		return json_encode(array('success' => true, 'dataTable' => 'tableFaq', 'link' => $this->url->action('index')));
	}

    /**
     * get button faq
     */
    function getButtons($faq)
    {
        $model = new Models_Faqs();
        $minNo = $model->db->select('min(orderno)')->where('category_id',$faq['category_id'])->getField();
        $maxNo = $model->db->select('max(orderno)')->where('category_id',$faq['category_id'])->getField();
        $buttons = array();

        if ($faq['orderno'] == $minNo)
        {
            $buttons[] = array(
                'onclick' => 'pushDown(this)',
                'id' => $faq['id'],
                'title' => 'MoveDown',
                'class' => 'icon-circle-arrow-down',
                'href' => 'javascript:'
            );
        }
        elseif ($faq['orderno'] == $maxNo)
        {
            $buttons[] = array(
                'onclick' => 'pushUp(this)',
                'id' => $faq['id'],
                'title' => 'MoveUp',
                'class' => 'icon-circle-arrow-up',
                'href' => 'javascript:'
            );
        }
        else
        {
            $buttons[] = array(
                'onclick' => 'pushDown(this)',
                'id' => $faq['id'],
                'title' => 'MoveDown',
                'class' => 'icon-circle-arrow-down',
                'href' => 'javascript:'
            );
            $buttons[] = array(
                'onclick' => 'pushUp(this)',
                'id' => $faq['id'],
                'title' => 'MoveUp',
                'class' => 'icon-circle-arrow-up',
                'href' => 'javascript:'
            );
        }
        $buttons[] = array(
            'href' => $this->url->action('edit', array('id' => $faq['id'],'type'=>@$this->params['type'])),
            'title' => 'Sửa',
            'class' => 'icon-edit'
        );

        $buttons[] = array(
            'href' => $this->url->action('delete', array('id' => $faq['id'],'type'=>@$this->params['type'])),
            'title' => 'Xóa',
            'class' => 'icon-trash frm-delete-btn'
        );
        return $buttons;
    }

    function getHtmlBtnAction()
    {
        $this->unloadLayout();
        $buttons = $this->getButtons($this->params['faq']);
        foreach ($buttons as $key => $button)
        {
            $this->tpl->insert_loop('main.button', 'button', $button);
        }
        return $this->view();
    }
	
	public function changeStatusAjax()
	{
		if(isset($this->params['status']))
		{
			$id = $this->params['id'];
			$status = $this->params['status'];
			$model = new Models_Faqs();
			if($status == 0)
			{
				$newStatus = 1;
				$html = "Hiển thị";
			}
			else if($status == 1)
			{
				$newStatus = 0;
				$html="Không hiển thị";
			}
			if(!$model->db->where('id',$id)->update(array('status'=>$newStatus)))
			{
				return json_encode(array('success'=>false,'msg'=>$this->model->error));
			}
			
			return json_encode(array('success'=>true,'html'=>$html, 'status'=>$newStatus));
		}
	}
	
	public function pushUpAjax()
	{
		if(isset($this->params['orderno']))
		{
			$id = $this->params['id'];
			$orderno = $this->params['orderno'];
			$idPrev = $this->params['idPrev'];
			$orderPrev = $this->params['orderPrev'];
			$model = new Models_Faqs();
			if(!$model->db->where('id',$id)->update(array('orderno'=>$orderPrev)) || !$model->db->where('id',$idPrev)->update(array('orderno'=>$orderno)) ) 
			{
				return json_encode(array('success'=>false,'msg'=>$model->error));
			}				
			return json_encode(array('success'=>true,'link'=>$this->url->action('index')));
		}
	}

    /**
     * sidebar faq category
     */
    public function sidebarCategoryAction()
    {
        $selected = @$this->params['id'];
        $modelCat = new Models_FaqsCategory();
        //Danh mục tin
        //$cats = $modelCat->getListCatMultiLevel(0, $_SESSION['sys_langcode']);
        if(MULTI_LANGUAGE)
            $modelCat->db->where('lang_code',@$_SESSION['sys_langcode']);
        $cats = $modelCat->db->select('id,name')
                ->orderby('orderno','asc')->getFieldsArray();
        foreach ($cats as $cat)
        {
            $cat['link'] = $this->url->action('index',array('catid'=>$cat['id']));
            $this->tpl->insert_loop('main.cat', 'cat', $cat);
        }
        $this->tpl->assign('listLink', $this->url->action('index'));

        $this->unloadLayout();
        return $this->view();
    }
}





