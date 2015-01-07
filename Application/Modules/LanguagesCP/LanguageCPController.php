<?php
class LanguageCPController extends Controller
{
	/**
	 * Change language CP
	 */
	public function changeLanguageAction()
	{
        if(MULTI_LANGUAGE)
        {
            $model = new Models_Language();
            $arrLang = $model->db->select('lang_code,name')->orderby('name')->getFieldsArray();
            foreach ($arrLang as $lang)
            {
                if (isset($_SESSION['sys_langcode']) && $lang['lang_code'] == $_SESSION['sys_langcode']) {
                    $this->tpl->assign('class', 'active');
                }
                else
                    $this->tpl->assign('class', '');
                $this->tpl->insert_loop('main.language', 'language', $lang);
            }
            $this->tpl->assign('changeLangLink', $this->url->action('changeLanguage'));
        }
		return $this->view();
	}
	
	public function changeLanguageAjax()
	{
		if (!empty($this->params['langcode'])) {
			$_SESSION['sys_langcode'] = $this->params['langcode'];
			return json_encode(array('success' => true));
		}
		else
			return json_encode(array('success' => false));
	}
	public function translateProductAction()
	{
		$model = new Models_Products();
		$model->Delete("`lang_code` = 'en-US'");
		$arrProducts = $model->db->where('lang_code','vi-VN')->getcFieldsArray();
		foreach ($arrProducts as $arrProduct)
		{
			unset($arrProduct['id']);
			if(!empty($arrProduct['category_id']))
				$arrProduct['category_id'] = $this->langCat($arrProduct['category_id']);
			$arrProduct['lang_code'] = "en-US";
			if(!$model->Insert($arrProduct))
			{
				echo $model->error;
				die;
			}
		}
	}
	function langCat($catVn)
	{
		switch ($catVn)
		{
			case 1:
				$catEn = 9;
				break;
			case 4:
				$catEn = 10;
				break;
			case 5:
				$catEn = 11;
				break;
			case 6:
				$catEn = 12;
			case 7:
				$catEn = 13;
				break;			
		}
		if(!empty($catEn))
			return $catEn;
	}
}