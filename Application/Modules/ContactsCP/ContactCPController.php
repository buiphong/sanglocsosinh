<?php
class ContactCPController extends Controller
{
	public $arrStatus = array('a' => 'Toàn bộ', '0' => 'Chưa đọc', '1' => 'Đã đọc');
	
	public function __init()
	{
		$this->loadTemplate('Metronic');
		$this->checkPermission();
	}
	
	public function indexAction()
	{
		$pageSize = 20;
	
		$page = @$this->params['page'];
		if (empty($page))
			$page = 1;
		
		$where = '';
		if (!empty($this->params['status']) && $this->params['status'] != 'a')
		{
			$where = 'status=' . $this->params['status'];
		}	

		if (!empty($where))
			$where = $where . ' and';
		$offset = ($page - 1) * $pageSize;
		$model = new Models_Contact();
        if(MULTI_LANGUAGE)
            $where .= " lang_code='".$_SESSION['sys_langcode']."'";
		$totalRows = $model->Count($where);
		$contacts = $model->db->where($where)->orderby('create_date','desc')->limit($pageSize,$offset)->getAll();
	
		if(!empty($contacts))
		{ 
			foreach ($contacts as $contact)
			{
				$this->tpl->assign('editLink',$this->url->action('edit',array('id'=>$contact['id'])));
				$contact['statusName'] = $this->arrStatus[$contact['status']];
				$contact['create_date'] = date('H:i d/m/Y', strtotime($contact['create_date']));
                $contact['title'] = String::HtmlStringEncode($contact['title']);
				$this->tpl->insert_loop('main.contact', 'contact', $contact);
			}
		}
	
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');		
		//Status
		foreach ($this->arrStatus as $key => $value)
		{
			$status['title'] = $value;
			$status['value'] = $key;
			if (isset($this->params['status']) && $this->params['status'] === (string)$key)
				$this->tpl->assign('checked', 'checked');
			else
				$this->tpl->assign('checked', '');
			$this->tpl->insert_loop('main.status', 'status', $status);
		}
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('frmAction', $this->url->action('index'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		
		return $this->view();
	}
	
	public function indexPost()
	{
		return $this->url->redirectAction('index', $this->params);
	}
	
	public function editAction()
	{
		$id = @$this->params['id'];
		if(!empty($id))
		{	
			$model = new Models_Contact($id);
			$this->tpl->assign('form_action',$this->url->action('edit'));
			$this->tpl->assign('listLink',$this->url->action('index'));
			$arrStatus = array(0=>'Chưa đọc', 1=>'Đã đọc');
			$this->tpl->assign('status',$this->html->genRadio('status', $arrStatus, $model->status));
		}
		return $this->view($model);
	}
	
	public function editPost(Models_Contact $model)
	{
		if(!empty($model->id))
		{
			if($this->model->Update())
			{
				$this->url->redirectAction('index');
			}
			else 
			{
				echo 'Can not update data: '.$this->model->error;
				die;
			}
		}
	}
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$ids = $this->params['listid'];
		$model = new Models_Contact();
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                    if(!$model->Delete("id=$id"))
                        return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}
	
	public function changeStatusAjax()
	{
		if (!empty($this->params['id']))
		{
			//Update status
			$id = $this->params['id'];
			if ($this->params['oldStatus'] == '0')
			{
				$status = 1;
				$statusName = 'Đã đọc';
			}
			else
			{
				$status = 0;
				$statusName = 'Chưa đọc';
			}
			$model = new Models_Contact();
			if($model->db->where('id', $id)->update(array('status'=>$status)))
				return json_encode(array('success' => true, 'status' => $status, 'statusName' => $statusName));
			else
				return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
		}
		return json_encode(array('success' => false, 'msg' => 'Thông tin nhập vào chưa chính xác'));
	}
    public function showDetailAjax()
    {
        $model = new Models_Contact();
        if(isset($_SESSION['sys_langcode']))
            $model->db->where('lang_code',$_SESSION['sys_langcode']);
        $contact = $model->db->select('title,content,status')
                    ->where('id',$this->params['id'])->getFields();
        $changeStatus = 0;
        if($contact['status'] == 0)
        {
            //Cập nhật trạng thái liên hệ sang đã xem
            $changeStatus = 1;
            Models_Contact::updateStatus($this->params['id'], 1);
        }
        if(!empty($contact))
            return json_encode(array('success'=>true,'data'=>$contact, 'changeStatus' => $changeStatus));
        else
            return json_encode(array('success'=>false));
    }
}