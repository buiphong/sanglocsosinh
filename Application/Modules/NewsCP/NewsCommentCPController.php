<?php
class NewsCommentCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Hiển thị danh sách bình luận theo trạng thái
	 */
	public function indexAction()
	{
		$pageSize = 10;
		$page = @$this->params['page'];
		if(empty($page))
			$page = 1;
		$offset = ($page - 1)*$pageSize;
	
		$modelComment = new Models_NewsComment();
        $modelNews = new Models_News();
		if(!empty($this->params['keySearch']))
		{
			$key = $this->params['keySearch'];
			$this->tpl->assign('keySearch', $key);			
		}
		$status = 0;
		if(!empty($this->params['slStatus']))
		{
			$status = $this->params['slStatus'];
		}

        $this->tpl->assign('newsTitle', 'Toàn bộ');
		if(!empty($this->params['newsId']))
		{
			$newId = $this->params['newsId'];
			$this->tpl->assign('news_id',$newId);
			$this->tpl->assign('backLink',$this->url->action('index','NewsCP','NewsCP'));
			$modelComment->db->where('news_id',$newId);
            $this->tpl->assign('newsTitle', $modelNews->db->select('title')->where('id', $this->params['newsId'])->getcField());
		}
		if($status != -1)
		{
			$modelComment->db->where('status',$status);
		}
		$totalRows = $modelComment->db->count();
		$arrComments = $modelComment->db->limit($pageSize, $offset)->orderby('created_date', 'desc')->getFieldsArray();
		$arrStatus = array('-1'=>'Toàn bộ',0=>'Chờ duyệt',1=>'Đã duyệt');
		$this->tpl->assign('actionLink',$this->url->action('index'));
		$this->tpl->assign('slStatus',$this->html->genSelect('slStatus', $arrStatus,$status,'','',array('class'=>'chosen-select')));
	    if($arrComments)
        {
            foreach ($arrComments as $arrComment)
            {
                $arrComment['newsTitle'] = $modelNews->db->select('title')->where('id', $arrComment['news_id'])->getcField();
                foreach ($arrStatus as $key=>$vl)
                {
                    if($arrComment['status'] == $key)
                    {
                        $arrComment['status_string'] = $vl;
                    }
                }
                $arrComment['content'] = strip_tags($arrComment['content']);
                $arrComment['sContent'] = String::get_num_word($arrComment['content'], 150);
                $arrComment['time'] = date('d/m/Y H:i', strtotime($arrComment['created_date']));
                $this->tpl->insert_loop('main.comment', 'comment', $arrComment);
            }
            $this->tpl->parse('main.button');
        }
		$this->tpl->assign('page', Helper::pagging($page, $pageSize, $totalRows));
        $this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}
	
	public function indexPost()
	{
		$this->url->redirectAction('index', $this->params);
	}
	/**
	 * Kiểm duyệt bình luận
	 */
	public function approveCommentAjax()
	{
		if (!empty($this->params['id']))
		{
			$id = $this->params['id'];
			$modelComment = new Models_NewsComment($id);
			//print_r($modelComment);
			$this->tpl->assign('form_action', $this->url->action('approveComment','NewsCommentCP','NewsCP'));
			if($this->params['view'] == 0)
				$this->tpl->parse('main.button');
			$this->unloadLayout();
			return json_encode(array('success' => true, 'html' => $this->view($modelComment)));
		}
		return json_encode(array('success' => false, 'msg' => 'Không tìm thấy thông tin'));
	}
	public function approveCommentPost()
	{
		if (!empty($this->params['id']))
		{
			echo $id = $this->params['id'];
			$modelComment = new Models_NewsComment();
			if($modelComment->db->where('id',$id)->update(array('status'=>1)))
			{				
				$this->url->redirectAction('index');
			}
			else
				die("Can not update data: ".$this->model->error);
		}
	}

    function deleteAjax()
    {
        $ids = $this->params['listid'];
        $model = new Models_NewsComment();
        if (strpos($ids, ',') !== false)
        {
            $ids = explode(',', $ids);
            foreach ($ids as $id)
                if ($id != '')
                if(!$model->Delete("id= $id"))
                {
                    return json_encode(array('success' => false, 'msg' => $model->error));
                    break;
                }
        }
        elseif ($ids != '')
        {
            if(!$model->Delete("id=$ids"))
                return json_encode(array('success' => false, 'msg' => $model->error));
        }
        return json_encode(array('success' => true, 'link' => $this->url->action('index')));
    }
}