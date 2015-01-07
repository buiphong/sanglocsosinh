<?php
class FaqComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule('FaqCP', 'ConfigCP');
    }

	public function categoryFaqAction(){
		$model = new Models_FaqsCategory();
		$categories = $model->db->select('name, id')->orderby('orderno')->getAll();
		foreach ($categories as $category){
            $category['link'] = $this->url->action('listFaq',
                array('catname' => String::seo($category['name']), 'catid' => $category['id']));
			$this->tpl->insert_loop('main.category', 'category', $category);
		}
		return $this->view();
	}

	public function listFaqAction(){
		$model = new Models_Faqs();
        //Get pagesize from config
        $display = @Models_ConfigValue::getConfValue('pagging_faq');
        if(!$display)
            $display = 5;
        $page = @$this->params['page'];
        if(!$page)
            $page = 1;
        $offset = ($page - 1) * $display;
        $catName = 'Giải đáp';
        $cn = '';
        if(isset($this->params['catid']) && !empty($this->params['catid']))
        {
            $model->db->where('category_id', $this->params['catid']);
            $cn = Models_FaqsCategory::getName($this->params['catid']);
            $catName .= ' - '. $cn;
        }
        $model->db->select("id, question, answer")->where('status', 1);
        $total = $model->db->count();
        $listFaq = $model->db->limit($display, $offset)->orderby("hits", 'desc')->getcFieldsArray();
        if($listFaq)
        {
            $i = $offset + 1;
            $arrHit = @$_SESSION['slss_hit_faq'];
            if($arrHit)
                $arrHit = unserialize($arrHit);
            foreach($listFaq as $k => $v)
            {
                $v['href'] = "javascript:";
                $v['num'] = $i;
                if(!empty($arrHit) && in_array($v['id'], $arrHit))
                    $v['dataHit'] = 1;
                else
                    $v['dataHit'] = 0;
                $this->tpl->insert_loop("main.faq", "faq", $v);
                $i++;
            }
        }

        $params = array("key" => "catid",'catname' => String::seo($cn), "value" => @$this->params['catid'],
            "page" => $page, "total" => $total, "pageSize" => $display);
        $html = $this->pagging($params);
        if(!empty($html))
            $this->tpl->assign("pagging", $html);

        $this->tpl->assign('captcha', Html::getCaptcha());
        $this->tpl->assign('boxTitle', $catName);
		return $this->view();
	}

	public function indexAction()
	{
		$model = new Models_Faqs();
		$faqs = $model->db->select('question, answer, fullname, created_time')->where('status',1)->orderby('orderno')->getAll();
		foreach ($faqs as $faq)
		{
			if(!empty($faq['fullname']))
			{
				$info['fullname'] = $faq['fullname'];
				$info['created_time'] = VccDateTime::userDateTime($faq['created_time']);
				$this->tpl->insert_loop('main.faq.infoFaq', 'infoFaq', $info);
			}
			$this->tpl->insert_loop('main.faq', 'faq', $faq);
		}
		return $this->view();
	}

    public function pagging($params = array())
    {
        $html = "";
        //Pagging
        if($params["total"] > $params["pageSize"])
        {
            $pageCount = ceil($params["total"]/$params["pageSize"]);
            if($pageCount > 1)
            {
                if($params["page"]> 1)
                    $html .= '<a class="page_btn" href="'.
                        $this->url->action('listFaq',
                            array($params["key"] => $params["value"],
                                "page" => $params["page"] - 1,'catname' => @$params['catname'])).
                        '" title="Trang"> < </a>';

                for($i=1; $i <= $pageCount; $i++)
                {
                    if($i == $params["page"])
                        $html .= '<span class="page_btn sp_current">'.$i.'</span>';
                    else
                        $html .= '<a class="page_btn" href="'.$this->url->action('listFaq',
                                array($params["key"] => $params["value"], "page" => $i, 'catname' => @$params['catname'])).'" title="">'.$i.'</a>';
                }
                if($params["page"] < $pageCount)
                    $html .= '<a class="page_btn" href="'.$this->url->action('listFaq',
                            array($params["key"] => $params["value"], "page" => $params["page"] + 1, 'catname' => @$params['catname'])).'" title=""> > </a>';
            }
        }
        return $html;
    }

    public function updateHitAjax()
    {
        if(isset($this->params['id']))
        {
            Models_Faqs::updateHits($this->params['id']);
            return json_encode(array('success' => true));
        }
        return json_encode(array('success' => false));
    }

    public function sendQuestionAjax()
    {
        if(!empty($this->params))
        {
            //Check for captcha
            if($this->html->validateCaptcha($this->params['captcha_code']))
            {
                $model = new Models_Faqs();
                $data = array(
                    'question' => $this->params['question'],
                    'fullname' => $this->params['fullname'],
                    'email' => $this->params['email'],
                    'created_time' => date('Y-m-d H:i:s'),
                    'status' => 0
                );
                if($model->Insert($data))
                    return json_encode(array('success'=>true, "msg" => "Cảm ơn bạn đã gửi câu hỏi.Chúng tôi đã nhận được câu hỏi của bạn và trả lời sớm nhất!"));
                else
                    return json_encode(array('success'=>false,'msg'=>$model->error));
            }
            else
                return json_encode(array("success" => false, "msg" => "Mã an toàn chưa đúng!"));
        }
        return json_encode(array("success" => false, "msg" => "Vui lòng nhập đầy đủ các thông tin cần thiết"));
    }
}