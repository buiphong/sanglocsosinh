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
       $page = @$this->params['page'];
       if (empty($page) || $page < 0)
           $page = 1;
       $offset = ($page - 1) * $pageSize;

       $model = new Models_NewsSpecial();
       $model->db->join('news','news.id=news_special.news_id');
       $modelType = new Models_NewsSpecialType();
       $modelCat = new Models_NewsCategory();
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
        if(!empty($param['typeId']))
        {
            $this->tpl->assign('linkCatNews', $this->url->action('getListNews', 'NewsComponent'));
            $pageSize = (!empty($param['num']))?$param['num']:3;
            $model = new Models_NewsSpecial();
            $modelType = new Models_NewsSpecialType();
            if($param['typeId'])
            {
                $this->tpl->assign('boxTitle',$modelType->db->select('title')->where('id',$param['typeId'])->getField());
                $model->db->where('news_special.special_type',$param['typeId']);
            }
            $datas = $model->db->select('news.id,news.title,news.image_path,news.brief,news.published_date')
                ->join('news', 'news_special.news_id=news.id')->where('news.status', 1)
                ->orderby('news_special.orderno')->limit($pageSize)->getFieldsArray();
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
        }
        return $this->view();
    }
}