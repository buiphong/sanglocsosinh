<?php
class NewsCommentController extends Presentation
{
	function frmCommentAction()
	{
		if(!empty($this->params['newsId']))
		{
			$pageSize = 2;
			$page = @$this->params['page'];
			if (empty($page) || $page < 0)
				$page = 1;
			$offset = ($page - 1) * $pageSize; 
			
			$id = $this->params['newsId'];
			$modelComment = new Models_NewsComment();
			$totalRow = $modelComment->Count("news_id = $id and status=1");
			$this->tpl->assign('totalComment',$totalRow);
			
			$modelComment->db->where('news_id',$id)->where('status',1);
			$comments = $modelComment->db->orderby('created_date','desc')->limit($pageSize,$offset)->getFieldsArray();
			
			if(!empty($comments))
			{
				foreach ($comments as $comment)
				{
					$comment['created_date'] = date('d/m/Y H:i', strtotime($comment['created_date']));
					$this->tpl->insert_loop('main.comments','comment',$comment);
				}
			}  
			
			$this->tpl->assign('newsId',$this->params['newsId']);
			//$this->tpl->assign('urlComment',$this->url->action('comment','NewsComment','news'));
			$this->tpl->assign('captcha',$this->html->showCaptcha());
			
			//pagging
			$this->tpl->assign('pageLink',$this->url->action('pagging','NewsComment','NewsCP'));
			$modelCat = new Models_NewsCategory();
			$this->tpl->assign('page', $this->html->renderAction('pagging', 'Component', 'Index',
					array('page' => $page, 'pageSize' => $pageSize, 'totalItem' => $totalRow)));
			return $this->view();
		}
	} 
	
	function commentAction()
	{
		if(!empty($this->params['newsId']))
		{
			$id = $this->params['newsId'];
			$modelComment = new Models_NewsComment();
			$comments = $modelComment->db->where('news_id',$id)->where('status',1)->orderby('created_date','desc')->getFieldsArray();
			if(!empty($comments))
			{
				foreach ($comments as $comment)
				{
                    $comment['content'] = htmlentities($comment['content']);
                    $comment['title'] = ($comment['title']);
                    echo var_dump($comment);
					$comment['created_date'] = VccDateTime::userDateTime($comment['created_date']);
					$this->tpl->insert_loop('main.comments.comment','comment',$comment);
				}
				$this->tpl->parse('main.comments');
			}
			$this->tpl->assign('newsId',$id);
			$this->tpl->assign('urlComment',$this->url->action('comment','NewsComment','News'));
			$this->tpl->assign('captcha',$this->html->showCaptcha());
			return $this->view();		
		}
	}
	
	function commentAjax(Models_NewsComment $model)
	{	
		if(!empty($this->params))
		{
			$captcha = @$this->params['captcha'];
			if(!$this->html->validateCaptcha($captcha))
			{
				return json_encode(array('success' => false, 'msg' => 'Mã xác nhận chưa đúng!', 'newCaptcha' => $this->html->showCaptcha()));
			}
			else
			{
				$model->news_id = @$this->params['newsId'];
				$model->created_date = date('Y/m/d h:i');
                $model->title = strip_tags($model->title);
                $model->content = strip_tags($model->content);
				if($model->Insert())
				{
					return json_encode(array('success' => true,'notice'=>'Ý kiến của bạn đã được gửi đi, cần chờ kiểm duyêt!'));
				}
				else 
				{
					return json_encode(array('success' => false, 'msg' => 'Lỗi: '.$model->error));
				}
			}
		}
		
	}	
	
	function paggingAjax()
	{
		if(!empty($this->params['page']))
		{
			$html = $this->html->renderAction('frmComment','NewsComment','News');
			return json_encode(array('success'=>true,'html'=>$html));
		}
		else
			return json_encode(array('success'=>false,'msg'=>'Không có dữ liệu!'));
	}
}