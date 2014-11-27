<?php
class NCatMenuController extends Controller
{
	public function indexAction()
	{
		//Get list category
		$Category = new Models_NewsCategory();
		$cats = $Category->getTreeCategory(0, false, @$_SESSION['sys_langcode']);
		return $this->html->genSelect('link_type_value', $cats, @$this->params['value']);
	}
}