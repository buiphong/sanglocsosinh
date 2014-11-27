<?php
class FaqCategoryCPController extends Controller
{
	public $arrStatus = array(0=>'Không hiển thị',1=>'Hiển thị');
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
        $this->loadModule(array('LanguagesCP'));
	}
	public function indexAction()
	{
        $pageSize = 10;

        $page = @$this->params['page'];
        if (empty($page))
            $page = 1;

        $offset = ($page - 1) * $pageSize;

        $model = new Models_FaqsCategory();

        $totalRows = $model->db->count();
		$cats = $model->db->select('id,name,orderno')->orderby('orderno')
                        ->limit($pageSize, $offset)->getAll();
		$i=1;
		foreach ($cats as $c)
		{
			if($i != 1)
			{
				$this->tpl->parse('main.faq.push');
			}
			if($i != count($cats))
			{
				$this->tpl->parse('main.faq.down');
			}
            $c['editLink'] = $this->url->action('edit', array('id' => $c['id']));
			$this->tpl->insert_loop('main.faq', 'faq', $c);
			$i++;
		}
        $this->tpl->assign('createLink',$this->url->action('create'));
        $this->tpl->assign('deleteLink',$this->url->action('delete'));
        $this->tpl->assign('pushUp',$this->url->action('pushUp'));
		if(count($cats) > 0)
		{
			$this->tpl->parse('main.button');
		}
        $this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		return $this->view();
	}

    public function indexPost()
    {
        $this->url->redirectAction('index', $this->params);
    }

	public function createAction()
	{
		$this->setView('edit');
        $modelLang = new Models_Language();
        $arrLang = $modelLang->db->select('lang_code,name')->orderby('orderno')->getFieldsArray();
        $this->tpl->assign('language',$this->html->genSelect('lang_code',$arrLang,@$_SESSION['sys_langcode'],'lang_code','name'));
		$this->tpl->assign('form_action',$this->url->action('create'));
        $this->tpl->assign('listLink',$this->url->action('index'));
		return $this->view();
	}
	
	public function createPost(Models_FaqsCategory $model)
	{
        if($this->model->Insert())
        {
            $this->url->redirectAction('index');
        }
        else
        {
            echo "Can not insert data: ".$this->model->error ;
            die;
        }
	}
	
	public function editAction()
	{
		if(!empty($this->params['id']))
		{
			$id = $this->params['id'];
            $model = new Models_FaqsCategory($id);

            $modelLang = new Models_Language();
            $arrLang = $modelLang->db->select('lang_code,name')->orderby('orderno')->getFieldsArray();
            $this->tpl->assign('language',$this->html->genSelect('lang_code',$arrLang,$model->lang_code,'lang_code','name'));
            $this->tpl->assign('form_action',$this->url->action('create'));

			$this->tpl->assign('form_action',$this->url->action('edit'));
            $this->tpl->assign('listLink',$this->url->action('index'));
			return $this->view($model);
		}
	}
	
	public function editPost(Models_FaqsCategory $model)
	{
		if(!empty($model->id))
		{
            $model->lang_code = $_SESSION['sys_langcode'];
			if($this->model->Update())
			{
				$this->url->redirectAction('index');
			}
			else
			{
				echo "Can not update data: ".$this->model->error ;
				die;
			}
		}
	}
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$model = new Models_FaqsCategory();
		$ids = $this->params['listid'];
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
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
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
			$model = new Models_FaqsCategory();
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
        $cats = $modelCat->db->select('id,name')->where('lang_code',$_SESSION['sys_langcode'])
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





