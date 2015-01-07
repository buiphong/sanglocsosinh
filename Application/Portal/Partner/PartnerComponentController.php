<?php
class PartnerComponentController extends Presentation
{
	public function listPartnerHomeAction()
	{
        $this->loadModule('PartnerCP');
		$model = new Models_Partner();
        $langCode = (isset($_SESSION['langcode']))?$_SESSION['langcode']:'vi-VN';
		//Lấy danh sách partner
		$partners = $model->db->select('id,name,image,orderno, link')->where('lang_code', $langCode)
							->orderby('orderno')->getFieldsArray();
		foreach ($partners as $partner)
		{
			$this->tpl->insert_loop('main.partner', 'partner', $partner);
		}
		return $this->view();
	}
}