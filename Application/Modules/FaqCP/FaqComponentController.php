<?php
class FaqComponentController extends Presentation
{
	public function indexAction()
	{
		$model = new Models_Faqs();
		$faqs = $model->db->select('question, answer, fullname, created_time')->where('status',1)->orderby('orderno')->getAll();
		foreach ($faqs as $faq)
		{
			if(!empty($faq['fullname']))
			{
				$info['fullname'] = $faq['fullname'];
				$info['created_time'] = PTDateTime::userDateTime($faq['created_time']);
				$this->tpl->insert_loop('main.faq.infoFaq', 'infoFaq', $info);
			}
			$this->tpl->insert_loop('main.faq', 'faq', $faq);
		}
		return $this->view();
	}
}