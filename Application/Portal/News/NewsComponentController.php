<?php
class NewsComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule(array('NewsCP'));
    }
	/**
	 * Chi tiết tin bài
	 */
	public function detailNewsAction()
	{
        if(empty($this->params['nid']))
            $id = @$this->params['id'];
        else
            $id = @$this->params['nid'];
		if (!empty($id))
		{
            $this->loadModule('NewsCP');
			$modelNews = new Models_News();
			$news = $modelNews->db->select('id,hits,title,brief,category_id,content,video_path,created_date,image_path,keywords,published_date,like,hascomment')
			->where('id', $id)->where('status',1)->getcFields();
			if($news)
			{
                $news['href'] = NewsHelper::getLinkNews($news);
				if(base64_decode($news['brief'], true))
					$news['brief'] = base64_decode($news['brief']);
				if(base64_decode($news['content'], true))
					$news['content'] = base64_decode($news['content']);
			
				if (!empty($news['video_path']))
				{
					$this->tpl->assign('video_path', $news['video_path']);
					$this->tpl->parse('main.video');
				}
                $news['dofw'] = PTDateTime::getDayOfWeek($news['published_date']);
				$news['date'] = date('d/m/Y', strtotime($news['published_date']));
                $news['time'] = date('H:i:s', strtotime($news['published_date']));
				$news['link'] = $news['href'];
				$this->tpl->assign('news', $news);
			
				$this->viewParam->title = @$news['title'];
				$this->viewParam->description = strip_tags($news['brief']);
				$this->viewParam->keywords = $news['keywords'];
				$this->viewParam->imgAvata = $this->url->getContentUrl($news['image_path']);

                //Kiểm tra tin liên quan
                $relative = Models_NewsRelative::getRelativeNews($news['id']);
                if($relative)
                {
                    foreach($relative as $v)
                    {
                        $v['link'] = NewsHelper::getLinkNews($v);
                        $this->tpl->insert_loop('main.relative.item', 'item', $v);
                    }
                    $this->tpl->parse('main.relative');
                }

                //Lấy 5 tin bài cùng danh mục
                $otherOld = Models_News::getNewsByCat($news['category_id'], 5, "id <> $id");
                if(!empty($otherOld))
                {
                    $count = 0;
                    foreach($otherOld as $onews)
                    {
                        $onews['link'] = NewsHelper::getLinkNews($onews);
                        $onews['date'] = date('d/m/Y', strtotime($onews['created_date']));
                        $this->tpl->insert_loop('main.other_news.item', 'item', $onews);
                        $count++;
                    }
                    if($count > 0)
                        $this->tpl->parse('main.other_news');
                }
            }	          
		}
		return $this->view();
	}

    /**
     * Chi tiết tin bài ajax
     */
    public function detailNewsAjax()
    {
        $id = @$this->params['id'];
        //$this->setView('newsAjax');
        $this->viewMode = 'single';
        if (!empty($id))
        {
            $this->loadModule('NewsCP');
            $modelNews = new Models_News();
            $news = $modelNews->db->select('id,hits,title,brief,category_id,content,video_path,created_date,image_path,keywords,published_date,like,hascomment')
                ->where('id', $id)->where('status', 1)->getcFields();
            if($news)
            {
                $news['href'] = NewsHelper::getLinkNews($news);
                if(base64_decode($news['brief'], true))
                    $news['brief'] = base64_decode($news['brief']);
                if(base64_decode($news['content'], true))
                    $news['content'] = base64_decode($news['content']);

                if (!empty($news['video_path']))
                {
                    $this->tpl->assign('video_path', $news['video_path']);
                    $this->tpl->parse('main.video');
                }
                $news['dofw'] = PTDateTime::getDayOfWeek($news['published_date']);
                $news['date'] = date('d/m/Y', strtotime($news['published_date']));
                $news['time'] = date('H:i:s', strtotime($news['published_date']));
                $news['link'] = $news['href'];
                $this->tpl->assign('news', $news);
            }
        }
        return json_encode(array('success' => true, 'html' => $this->view()));
    }

	/**
	 * Box danh sách tin theo danh mục  */
	function listNewsCatAction()
	{
		$modelNews = new Models_News();
		$modelCat = new Models_NewsCategory();
        $page = @$this->params['page'];
        if(!$page)
            $page = 1;
        $date = @$this->params['date'];
        $month = @$this->params['month'];
        $year = @$this->params['year'];
		$display = @$this->params['display'];
        if(!$display)
            $display = 5;
        //Check phân box hiển thị
        $dspSegment = array();
        if(strpos($display, '/') !== false)
        {
            $arr = explode('/', $display);
            $display = 0;
            $start = 0;
            foreach($arr as $k => $v)
            {
                $display += $v;
                $dspSegment[$k] = array(
                    'num' => $v,
                    'start' => $start + 1,
                    'end' => $start + $v
                );
                $start = $start + $v;
            }
        }
        $offset = ($page - 1) * $display;
		$catId = @$this->params['catId'];
        $cat = $modelCat->db->select('id,url_title,title,description')->where('id',$catId)->getcFields();
        $this->tpl->assign('boxTitle',$cat['title']);
        $this->tpl->assign('boxHref',NewsHelper::getLinkCat($cat));
        $arrCatId = $modelCat->db->select('id')->where('parent_id',$catId)->or_where('id',$catId)->getcFieldArray();
        $strCatId = implode(',', $arrCatId);
        $currDate = date('Y-m-d', time());
        if(!empty($strCatId))
        {
            $modelNews->db->where('category_id in('.$strCatId.')');
            if(!empty($date) && !empty($month) && !empty($year))
            {
                $currDate = $year . '-' . $month . '-' . $date;
                $modelNews->db->where('date(published_date)', $year . '-' . $month . '-' . $date);
            }
            $modelNews->db->where('status',1);
            $totalRow = $modelNews->db->count();
            if($totalRow > 0)
            {
                $newses = $modelNews->db->orderby('published_date','desc')->limit($display, $offset)->getcFieldsArray();
                $i = 1;
                $total = count($newses);
                foreach ($newses as $key => $news)
                {
                    $news['href'] = NewsHelper::getLinkNews($news);
                    if(base64_decode($news['brief'], true))
                        $news['brief'] = base64_decode($news['brief']);
                    $news['brief'] = strip_tags($news['brief']);
                    $news['date'] = date('d/m/Y', strtotime($news['published_date']));
                    if($i == $total)
                        $news['boxClass'] = 'last';
                    if(!empty($dspSegment))
                    {
                        foreach($dspSegment as $k => $v)
                        {
                            $k = $k + 1;
                            if($i <= $v['end'] && $i >= $v['start'])
                            {
                                if($v['num'] > 1)
                                    $this->tpl->insert_loop('main.segment' . $k, 'item' . $k, $news);
                                elseif($v['num'] == 1)
                                {
                                    $this->tpl->assign('item' . $k, $news);
                                    $this->tpl->parse('main.segment' . $k);
                                }
                                break;
                            }
                        }
                    }
                    else
                        $this->tpl->insert_loop('main.news','news',$news);
                    $i++;
                }

                $params = array("key" => "catId",'url_title' => $cat['url_title'], "value" => $catId, "page" => $page, "totalNews" => $totalRow, "pageSize" => $display);
                $html = $this->paggingNews($params);
                if(!empty($html))
                    $this->tpl->assign("pagging", $html);
            }
            else
            {
                $this->tpl->parse('main.empty');
            }
            //Get link by date
            $prevDate = strtotime($currDate. ' - 1 days');
            $nextDate = strtotime($currDate. ' + 1 days');
            $this->tpl->assign('urlPrevDate', $this->url->action('listNewsCat', array('catname' => $cat['url_title'],
                'catId' => $cat['id'], 'date' => date('d', $prevDate), 'month' => date('m', $prevDate), 'year' => date('Y', $prevDate))));
            $this->tpl->assign('urlNextDate', $this->url->action('listNewsCat', array('catname' => $cat['url_title'],
                'catId' => $cat['id'], 'date' => date('d', $nextDate), 'month' => date('m', $nextDate), 'year' => date('Y', $nextDate))));
        }
        if(!empty($date) && !empty($month) && !empty($year))
            $cat['title'] .= ' - ' . $date . '/' . $month . '/' . $year;
        $this->tpl->assign('date', date('d/m/Y', time()));
        $this->tpl->assign('baseUrl', $this->url->action('listNewsCat', array('catId' => $cat['id'], 'catname' => $cat['url_title'])));
        $this->viewParam->title = $cat['title'];
		return $this->view();
	}

    public function listNewsCatAjaxAction()
    {
        $modelNews = new Models_News();
        $modelCat = new Models_NewsCategory();
        $display = @$this->params['display'];
        $catId = @$this->params['catId'];
        $cat = $modelCat->db->select('title,description')->where('id',$catId)->getcFields();
        $this->tpl->assign('boxTitle',$cat['title']);
        $arrCatId = $modelCat->db->select('id')->where('parent_id',$catId)->or_where('id',$catId)->getcFieldArray();
        $strCatId = implode(',', $arrCatId);
        if(!empty($strCatId))
        {
            $newses = $modelNews->db->select('id,title')->where('status',1)->where('category_id in('.$strCatId.')')->orderby('published_date','desc')->limit($display)->getcFieldsArray();
            foreach ($newses as $key => $news)
            {
                $this->tpl->insert_loop('main.news','news',$news);
            }

            //Lấy danh sách tin khác
        }
        $this->viewParam->title = $cat['title'];
        return $this->view();
    }

    //Tìm kiếm
    public function listSearchDetailAction()
    {
        $time = microtime(true);
        $lucene_index_path = ROOT_PATH . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'DataNews';
        $modelCat = new Models_NewsCategory();
        setlocale(LC_CTYPE, 'utf-8');
        //Gán cho kiểu dữ liệu không phân biệt chữ hoa và chữ thường.
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive());
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');

        $index = Zend_Search_Lucene::open($lucene_index_path);

        $txtKeySearch = strtolower(String::removeSign($this->params['txtKeySearch']));
        $listKeys = explode(' ', $txtKeySearch);

        $query = new Zend_Search_Lucene_Search_Query_Phrase();
        foreach($listKeys as $listKey){
            $query->addTerm(new Zend_Search_Lucene_Index_Term($listKey, 'flattitle'));
        }

        $hits  = $index->find($query);
        $briefLength = 260;
        if(!empty($hits))
        {
            $num=1;
            foreach ($hits as $hit){
                $strCat = $modelCat->getCatParent($hit->category_id);
                foreach ($strCat as $vl){
                    $strCat = $vl;
                }
                $news = array();
                $news['num'] = $num;
                $num ++;
                $news['id'] = $hit->newsid;
                $news['imagepath'] = $this->url->thumbnail($hit->imagePath,286,200);
                $news['brief'] = $hit->flatbrief;
                if(base64_decode($news['brief'], true))
                    $news['brief'] = base64_decode($news['brief']);
                $news['brief'] = String::get_num_word(strip_tags($news['brief']), $briefLength);
                $news['category_id'] = $hit->category_id;

                $news['title'] = $hit->title;
                if(base64_decode($news['title'], true))
                    $news['title'] = base64_decode($news['title']);

                $news['publisheddate'] = date('d/m/Y H:i', $hit->publishedDate);
                $news['href'] = NewsHelper::getLinkNews($news);
                $this->tpl->insert_loop('main.search','search',$news);
            }
            $this->tpl->assign('total', count($hits));
            $extime = microtime(true) - $time;
            $this->tpl->assign('searchTime', ($extime) . '(s)');

            //$this->tpl->assign('msg', 'Tìm thấy ' . count($hits) . ' kết quả trong ' . number_format($extime, 4) . ' giây');
            $this->tpl->assign('msg', 'Tìm thấy ' . count($hits) . ' kết quả cho từ khóa:  "<span>' .$this->params['txtKeySearch']. '</span>"');

            //page
            $pageSize = 5;
            $this->tpl->assign('pageSize',$pageSize);
            $totalRows = count($hits);
            if($totalRows > $pageSize)
                $this->tpl->assign('pagging',$this->page($totalRows,$pageSize));
        }
        else
            $this->tpl->assign('msg', 'Không tìm thấy kết quả nào cho từ khóa: "<span>' . $this->params['txtKeySearch'] . '</span>"');
        return $this->view();
    }
    public function page($totalRows,$pageSize)
    {
        $pagecount = intval($totalRows/$pageSize); if (($totalRows%$pageSize)>0) $pagecount++;
        if($pagecount > 1)
        {
            $html="";
            for($i=1; $i <= $pagecount; $i++)
            {
                //$class = ($page == $i)?'class="active"': '';
                $html .= '<a href="javascript:" class="pageLink" title="'.@$_SESSION['webConfig']['conf_seo_title']['value'].'" accesskey="'.$i.'" onclick="loadPage(this)" >'.$i.'</a>';
            }
            return $html;
        }
    }

    public function paggingNews($params = array())
    {
        $html = "";
        //Pagging
        if($params["totalNews"] > $params["pageSize"])
        {
            $pageCount = ceil($params["totalNews"]/$params["pageSize"]);
            if($pageCount > 1)
            {
                if($params["page"]> 1)
                    $html .= '<a class="page_btn" href="'.
                        $this->url->action('listNewsCat',
                            array($params["key"] => $params["value"],
                                "page" => $params["page"] - 1,'catname' => @$params['url_title'])).
                        '" title="Trang"> < </a>';

                for($i=1; $i <= $pageCount; $i++)
                {
                    if($i == $params["page"])
                        $html .= '<span class="page_btn sp_current">'.$i.'</span>';
                    else
                        $html .= '<a class="page_btn" href="'.$this->url->action('listNewsCat',
                                array($params["key"] => $params["value"], "page" => $i, 'catname' => @$params['url_title'])).'" title="">'.$i.'</a>';
                }
                if($params["page"] < $pageCount)
                    $html .= '<a class="page_btn" href="'.$this->url->action('listNewsCat',
                            array($params["key"] => $params["value"], "page" => $params["page"] + 1, 'catname' => @$params['url_title'])).'" title=""> > </a>';
            }
        }
        return $html;
    }

    /**
     * Box tin bài mới nhất
     */
    public function boxLatestNewsAction()
    {
        $display = (!empty($this->params["display"]))?$this->params["display"]:10;
        $listNews = Models_News::getListLatestNews($display);
        foreach($listNews as $value)
        {
            $value["href"] = NewsHelper::getLinkNews($value);
            $this->tpl->insert_loop("main.news", "news", $value);
        }
        $this->tpl->assign("listNews", $this->url->action("getListNews"));
        return $this->view();
    }

    /**
     * Box danh sách danh mục con
     */
    public function listChildCatAction()
    {
        if(!isset($this->params['catid']))
        {
            $router = new Router();
            $router->analysisRequestUri();
            if($router->action == 'getListNews' && !empty($router->args['ncatid']))
                $catId = $router->args['ncatid'];
            elseif($router->action == 'detailNews' && !empty($router->args['nid']))
                $catId = Models_News::getcField("select category_id from news where id=" . $router->args['nid']);
        }
        else
            $catId = $this->params['catid'];
        if(!empty($catId))
        {
            $model = new Models_NewsCategory();
            $cat = Models_NewsCategory::getById($catId);
            if(!empty($cat['parent_id']))
            {
                $listCat = $model->db->select('id,title,url_title')->where('parent_id', $cat['parent_id'])->getFieldsArray();
                $cat = Models_NewsCategory::getById($cat['parent_id']);
            }
            else
                $listCat = $model->db->select('id,title,url_title')->where('parent_id', $cat['id'])->getFieldsArray();

            $this->tpl->assign('parent', $cat);
            if($listCat)
            {
                foreach($listCat as $c)
                {
                    $c['link'] = NewsHelper::getLinkCat($c);
                    $this->tpl->insert_loop('main.category.list', 'item', $c);
                }
                $this->tpl->parse('main.category');
            }
        }
        return $this->view();
    }
}