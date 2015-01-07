<?php
class DocumentController extends Presentation
{
    public function __init()
    {
        $this->loadModule('DocumentsCP');
    }

    /**
     * Chi tiết tài liệu
     */
    public function detailAction()
    {
        $id = @$this->params['id'];
        if (!empty($id))
        {
            $this->loadModule('DocumentsCP');
            $model = new Models_Documents();
            $doc = $model->db->select('id,title,description,category,content,image,published_time,status,file_url,file_info,member')
                ->where('id', $id)->getcFields();
            if($doc && $doc['status'] == 1)
            {
                $doc['href'] = DocHelper::getLinkDoc($doc);
                if(base64_decode($doc['description'], true))
                    $news['description'] = base64_decode($doc['description']);
                if(base64_decode($doc['content'], true))
                    $doc['content'] = base64_decode($doc['content']);

                if($doc['file_url'])
                {
                    $doc['file_info'] = unserialize($doc['file_info']);
                    $doc['file_info']['size'] = $doc['file_info']['size'] / 1024;
                    $doc['file_class'] = '';
                    if(!isset($_SESSION['member']) && $doc['member'] == 1)
                        $doc['file_url'] = "javascript:alert('Chức năng download tài liệu chỉ dành cho thành viên, vui lòng đăng nhập trước!')";
                }

                $doc['dofw'] = PTDateTime::getDayOfWeek($doc['published_time']);
                $doc['date'] = date('d/m/Y', strtotime($doc['published_time']));
                $doc['time'] = date('H:i:s', strtotime($doc['published_time']));
                $doc['link'] = $doc['href'];
                $this->tpl->assign('doc', $doc);

                $this->viewParam->title = @$doc['title'];
                $this->viewParam->description = strip_tags($doc['description']);
                $this->viewParam->imgAvata = $this->url->getContentUrl($doc['image']);

                //Lấy 5 tài liệu cùng danh mục
                $otherOld = Models_Documents::getDocByCat($doc['category'], 5, "id <> $id");
                if(!empty($otherOld))
                {
                    $count = 0;
                    foreach($otherOld as $odoc)
                    {
                        $odoc['link'] = DocHelper::getLinkDoc($odoc);
                        $odoc['date'] = date('d/m/Y', strtotime($odoc['created_time']));
                        $this->tpl->insert_loop('main.other_doc.item', 'item', $odoc);
                        $count++;
                    }
                    if($count > 0)
                        $this->tpl->parse('main.other_doc');
                }
            }
        }
        return $this->view();
    }

    /**
     * Danh sách tài liệu theo danh mục
     */
    public function listDocAction()
    {
        $this->tpl->assign('boxTitle', 'Tài liệu');
        $display = @$this->params['display'];
        if(!$display)
            $display = 10;

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

        $page = @$this->params['page'];
        if(!$page)
            $page = 1;
        $offset = ($page - 1) * $display;
        $model = new Models_Documents();
        if(isset($this->params['catid']) && !empty($this->params['catid']))
        {
            $cat = Models_DocumentsCategory::getById($this->params['catid']);
            $model->db->where('category', $this->params['catid']);
            $this->tpl->assign('boxTitle', $cat['title']);
        }
        $model->db->where('status', 1);
        $list = $model->db->select('id,title,image,description,category,file_info,file_url,member')->limit($display, $offset)->orderby('published_time')
                ->getFieldsArray();
        if($list)
        {
            $i = 1;
            foreach($list as $doc)
            {
                $doc['href'] = DocHelper::getLinkDoc($doc);
                $doc['file_class'] = 'dpn';
                if($doc['file_url'])
                {
                    $doc['file_info'] = unserialize($doc['file_info']);
                    $doc['file_info']['size'] = $doc['file_info']['size'] / 1024;
                    $doc['file_class'] = '';
                    if(!isset($_SESSION['member']) && $doc['member'] == 1)
                        $doc['file_url'] = "javascript:alert('Chức năng download tài liệu chỉ dành cho thành viên, vui lòng đăng nhập trước!')";
                }

                if(!empty($dspSegment))
                {
                    foreach($dspSegment as $k => $v)
                    {
                        $k = $k + 1;
                        if($i <= $v['end'] && $i >= $v['start'])
                        {
                            if($v['num'] > 1)
                                $this->tpl->insert_loop('main.segment' . $k, 'item' . $k, $doc);
                            elseif($v['num'] == 1)
                            {
                                $this->tpl->assign('item' . $k, $doc);
                                $this->tpl->parse('main.segment' . $k);
                            }
                            break;
                        }
                    }
                }
                else
                {
                    $this->tpl->insert_loop('main.doc','doc',$doc);
                }
                $i++;
            }
        }
        return $this->view();
    }
}