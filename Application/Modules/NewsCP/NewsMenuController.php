<?php
class NewsMenuController extends Controller
{
	public function indexAction()
	{
        $model = new Models_News();
		if(isset($_SESSION['sys_langcode']))
            $model->db->where('lang_code', $_SESSION['sys_langcode']);
		//Danh sách tin tức
		$newses = $model->db->select('id,title')->where('status', 1)->getFieldsArray();
		return $this->html->genSelect('link_type_value', $newses,@$this->params['value'], 'id', 'title', array('class' => 'chosen-select', 'style' => 'width: 400px;'));
	}
}