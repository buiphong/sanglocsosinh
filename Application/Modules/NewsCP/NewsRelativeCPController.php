<?php
/**
 * Tin liên quan
 * @author phongbui
 */
class NewsRelativeCPController extends Controller
{
    public function indexAction()
    {
        $this->loadTemplate('Metronic');
        $this->loadLayout('index');
        if(isset($this->params['newsId']))
        {
            $news = Models_News::getById($this->params['newsId']);
            $this->tpl->assign('news', $news);
            $_SESSION['sys_selected_news'] = $news;
        }
        $this->tpl->assign('listLink', $this->url->action('index', 'NewsCP'));
        $this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('homeLink', $this->url->action('index', 'Index', 'ControlPanel'));
        return $this->view();
    }

    public function listAjax()
    {
        $model = new Models_NewsRelative();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];
        $newsId = $this->params['newsId'];
        if(!empty($this->params['sSearch']))
            $model->db->like('news.title', $this->params['sSearch']);
        $model->db->join('news', 'news.id=news_relative.relative_news_id');
        $model->db->join('news_category', 'news_category.id=news.category_id');
        //Get list by news
        if (!empty($newsId))
        {
            $model->db->where('news_relative.news_id', $newsId);
        }
        /*Ordering*/
        $model->db->orderby('order_no');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('news_relative.id,news_relative.news_id as newsid,news.title,news_category.title as category,news_relative.order_no')
            ->limit($pageSize,$offset)->getFieldsArray();
        foreach($datas as $k=>$v)
        {
            $datas[$k]['btn'] = $this->html->renderAction('getHtmlBtn', array('news' => $v));
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

    public function deleteAjax()
    {
        $ids = $this->params['id'];
        $model = new Models_NewsRelative();
        if (strpos($ids, ',') !== false)
        {
            $ids = explode(',', $ids);
            foreach ($ids as $id)
                if ($id != '')
                    if(!$model->Delete("id= $id"))
                        return json_encode(array('success' => false, 'msg' => $model->error));
        }
        elseif ($ids != '')
        {
            if(!$model->Delete("id=$ids"))
                return json_encode(array('success' => false, 'msg' => $model->error));
        }
        return json_encode(array('success' => true, 'dataTable' => 'tableRelativeNews'));
    }

    public function showSelectNewsAjax()
    {
        return $this->view();
    }

    public function listSelectNewsAjax()
    {
        $model = new Models_News();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];
        if(!empty($this->params['sSearch']))
            $model->db->like('news.title', $this->params['sSearch']);
        $model->db->where('news.status', 1);
        $model->db->join('news_category', 'news_category.id=news.category_id');
        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('published_date', 'desc');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->select('news.id,news.title,news_category.title as category')
            ->limit($pageSize,$offset)->getFieldsArray();

        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

    public function selectNewsAjax()
    {
        if(isset($this->params['listId']))
        {
            //Add tin liên quan
            $result = Models_NewsRelative::addNews($this->params['listId'], $_SESSION['sys_selected_news']['id']);
            if(!$result)
                return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi lựa chọn tin'));
            else
                return json_encode(array('success' => true, 'msg' => 'Cập nhật tin liên quan cho tin bài `'.
                    $_SESSION['sys_selected_news']['title'].'` thành công'));
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Chưa có tin nào được chọn'));
    }

    /**
     * Movedown news
     */
    public function moveDownAjax()
    {
        if (!empty($this->params['id'])) {
            $model = new Models_NewsRelative();
            $news = $model->db->select('id, order_no, news_id, relative_news_id')->where('id',$this->params['id'])->getFields();
            //update orderno
            $sql = "update news_relative set order_no=" . $news['order_no'] . "
					where news_id='".$news['news_id']."'
							and order_no=" . ((int)$news['order_no'] + 1);
            if($model->db->Execute($sql))
                if($model->db->where('id',$news['id'])->update(array('order_no' => ((int)$news['order_no'] + 1))))
                    return json_encode(array('success' => true));
            return json_encode(array('success' => false, 'msg' => $model->error));
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa đúng'));
    }

    /**
     * MoveUp news
     */
    public function moveUpAjax()
    {
        if (!empty($this->params['id'])) {
            $model = new Models_NewsRelative();
            $news = $model->db->select('id, order_no, news_id, relative_news_id')->where('id',$this->params['id'])->getFields();
            //update orderno
            $sql = "update news_relative set order_no=" . $news['order_no'] . "
					where news_id='".$news['news_id']."'
							and order_no=" . ((int)$news['order_no'] - 1);
            if($model->db->Execute($sql))
                if($model->db->where('id',$news['id'])->update(array('order_no' => ((int)$news['order_no'] - 1))))
                    return json_encode(array('success' => true));
            return json_encode(array('success' => false, 'msg' => $model->error));
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Dữ liệu nhập vào chưa đúng'));
    }

    /**
     * get button special news
     */
    function getButtons($news)
    {
        /*$maxNo = $this->db->GetFieldValue("select max(orderno) as maxValue from news_special
                where special_type='".$sNews['special_type']."' and category_id='".$sNews['category_id']."'");*/
        $model = new Models_NewsRelative();
        $minNo = $model->db->select('min(order_no)')->where('news_id',$news['newsid'])
            ->getField();
        $maxNo = $model->db->select('max(order_no)')->where('news_id',$news['newsid'])
            ->getField();
        $buttons = array();
        if ($news['order_no'] == $minNo)
        {
            $buttons[] = array(
                'onclick' => 'moveDownRNews(this)',
                'id' => $news['id'],
                'title' => 'MoveDown',
                'class' => 'icon-circle-arrow-down',
                'href' => 'javascript:'
            );
        }
        elseif ($news['order_no'] == $maxNo)
        {
            $buttons[] = array(
                'onclick' => 'moveUpRNews(this)',
                'id' => $news['id'],
                'title' => 'MoveUp',
                'class' => 'icon-circle-arrow-up',
                'href' => 'javascript:'
            );
        }
        else
        {
            $buttons[] = array(
                'onclick' => 'moveDownRNews(this)',
                'id' => $news['id'],
                'title' => 'MoveDown',
                'class' => 'icon-circle-arrow-down',
                'href' => 'javascript:'
            );
            $buttons[] = array(
                'onclick' => 'moveUpRNews(this)',
                'id' => $news['id'],
                'title' => 'MoveUp',
                'class' => 'icon-circle-arrow-up',
                'href' => 'javascript:'
            );
        }
        $buttons[] = array(
            'href' => $this->url->action('delete', array('id' => $news['id'])),
            'title' => 'Xóa',
            'id' => $news['id'],
            'class' => 'icon-trash frm-delete-btn'
        );
        return $buttons;
    }

    function getHtmlBtnAction()
    {
        $this->unloadLayout();
        $buttons = $this->getButtons($this->params['news']);
        foreach ($buttons as $key => $button)
        {
            $this->tpl->insert_loop('main.button', 'button', $button);
        }
        return $this->view();
    }
}