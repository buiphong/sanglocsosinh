<?php
/**
 * Tin sắp xếp
 * @author buiphong
 *
 */
class SNewsComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule('NewsCP');
    }
	/**
	 * Danh sách tin sắp xếp
	 */
	public function listSNewsAction()
	{
        $pageSize = (!empty($this->params['num']))?$this->params['num']:6;
        $page = empty($this->params['page']) ? 1 : $this->params['page'];
        $offset = ($page - 1) * $pageSize;

        $model = new Models_NewsSpecial();
        $model->db->join('news','news.id=news_special.news_id');
        $modelType = new Models_NewsSpecialType();
        if (!empty($this->params['typeId']))
        {
           $typeId = $this->params['typeId'];
           $model->db->where('news_special.special_type',$typeId);
           $this->tpl->assign('typeName',$modelType->db->select('title')->where('id',$typeId)->getField());
        }
        $totalRow = $model->db->count();
        $newses = $model->db->select('news_special.title,news_special.id,news_special.news_id,news.content,news_special.brief,
        news_special.url_title,news_special.category_id,news_special.image_path')->limit($pageSize,$offset)
           ->where('news.status', 1)->orderby('news_special.orderno')->getFieldsArray();
        if(!empty($newses))
        {
           foreach ($newses as $key=>$news)
           {
               $news['class'] = (($key+1)%2 == 0)?'':'mr10';
               if($key == 1)
                   $this->tpl->parse('main.news.line');
               $news['url'] = NewsHelper::getLinkNews($news['news_id']);
               if(base64_decode($news['brief'], true))
                   $news['brief'] = base64_decode($news['brief']);
               if(base64_decode($news['content'], true))
                   $news['content'] = base64_decode($news['content']);
               $this->tpl->insert_loop('main.news', 'news', $news);
           }
        }
        if($totalRow / $pageSize > 1)
        {
            $this->tpl->assign('paging', $this->html->renderAction('pagging', 'Component', 'Index',
               array('page' => $page, 'pageSize' => $pageSize, 'totalItem' => $totalRow ,'router'=>$this->router,'params'=>$this->params,'pageParamName'=>'page')));
            $this->tpl->parse('main.paging');
        }
	    return $this->view();
	}

    /*Box tin sắp xếp trang chủ*/
    public function boxSNewsAction()
    {
        $param = @$this->params;
        if(isset($param['skin']) && !empty($param['skin']))
            $this->setView($param['skin'], true);
        if(!empty($param['typeId']))
        {
            $this->tpl->assign('linkCatNews', $this->url->action('getListNews', 'NewsComponent'));
            $totalDisplay = (!empty($param['num']))?$param['num']:3;
            $pageSize = (!empty($param['display']))?$param['display']:3;
            $page = empty($this->params['page']) ? 1 : $this->params['page'];
            $offset = ($page - 1) * $pageSize;
            if($param['typeId'])
            {
                $type = Models_NewsSpecialType::getById($param['typeId']);
                $this->tpl->assign('boxTitle',$type['title']);
            }
            $datas = Models_NewsSpecial::getListNews($param['typeId'], $pageSize, $offset);
            $totalRow = Models_NewsSpecial::countByType($param['typeId']);
            if($totalRow > $totalDisplay)
                $totalRow = $totalDisplay;
            else
                $totalDisplay = $totalRow;
            if($datas)
            {
                $i = 1;
                $total = count($datas);
                foreach($datas as $key=>$data)
                {
                    $data['href'] = NewsHelper::getLinkNews($data['id']);
                    if(base64_decode($data['brief'], true))
                        $data['brief'] = base64_decode($data['brief']);
                    $data['date'] = date('d/m/Y', strtotime($data['published_date']));
                    $data['boxClass'] = '';
                    if($i == 3 || $i == 4)
                        $data['boxClass'] = 'boxBlue';
                    if($i % 2 == 0)
                        $this->tpl->parse('main.news.clear2');

                    if($i == $total)
                        $data['boxClass'] .= ' last';
                    if($i == 1)
                    {
                        $this->tpl->assign('item', $data);
                        $this->tpl->parse('main.top');
                    }
                    else
                        $this->tpl->insert_loop('main.news','news',$data);
                    $i++;
                }
            }
            if($totalRow / $pageSize > 1)
            {
                $paging = Helper::getPaging($totalRow, $pageSize, $page, 3);
                for($i = $paging['start']; $i <= $paging['end']; $i++) {
                    $this->tpl->assign('class', '');
                    if($i == $page)
                        $this->tpl->assign('class', 'sp_current');
                    $this->tpl->insert_loop('main.ajaxPaging', 'page', $i);
                }
            }
            $this->tpl->assign('ajaxUrl', $this->url->action('boxSNews'));
            $this->tpl->assign('ajaxParams', base64_encode(serialize(
                array('typeId' => $param['typeId'], 'limit' => $pageSize, 'num' => $totalDisplay,
                    'page' => $page, 'skin' => $this->tpl->skin))));
        }
        return $this->view();
    }

    public function boxSNewsAjax()
    {
        $params = unserialize(base64_decode($this->params['data']));
        $data = array('typeId' => $params['typeId'], 'page' => $params['page'] + 1, 'display' => $params['limit'],
            'skin' => $params['skin'], 'num' => $params['num']);
        return json_encode(array('success' => true, 'html' => $this->html->renderAction('boxSNews', $data)));
    }
}