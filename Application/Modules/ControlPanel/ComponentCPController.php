<?php
class ComponentCPController extends Controller
{
	public function __init()
	{
		$this->checkPermission();
        $this->loadModule(array('NewsCP'));
	}
	
	/**
	 * Icon thông báo header
	 */
	public function headerNotifyIconAction()
	{
		//Get current date
		$this->tpl->assign('date', date('d F Y'));
		$this->tpl->assign('time', date('l, H:i'));
		//check new News's comment
		$model = new Models_NewsComment();
		$comment = $model->db->where('status', 0)->count(true);
		if ($comment > 0) {
			$this->tpl->assign('comment', $comment);
			$this->tpl->assign('commentLink', $this->url->action('index', 'NewsCommentCP', 'NewsCP'));
			$this->tpl->parse('main.newsComment');
		}
        if(Helper::moduleExist('ContactsCP'))
        {
            $this->loadModule('ContactsCP');
            //count unread contact
            $modelContact = new Models_Contact();
            $total = $modelContact->db->where('status <>', 1)->count(true);
            if($total)
            {
                $this->tpl->assign('contact', $total);
                $this->tpl->assign('contactLink', $this->url->action('index', 'ContactCP', 'ContactsCP'));
                $this->tpl->parse('main.newContact');
            }
        }
        if(Helper::moduleExist('OrderCP'))
        {
            $this->loadModule('OrderCP');
            $modelOrder = new Models_Order();
            $noticeOrder = $modelOrder->db->where('status', 0)->count();
            if($noticeOrder > 0)
            {
                $this->tpl->assign('numberOrder', $noticeOrder);
                $this->tpl->assign('noticeOrderLink', $this->url->action('index', 'OrderCP', 'OrderCP', array('order'=>'noticeOrder')));
            }
            $this->tpl->parse('main.noticeOrder');
        }
		return $this->view();	
	}
}