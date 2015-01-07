<?php
class MemberTypeCPController extends Controller
{
    function __init()
    {
        $this->checkPermission();
        $this->loadTemplate('Metronic');
    }

    function indexAction()
    {
        $model = new Models_MemberType();
        $pageSize = 20;
        $where = "";
        # page navigation
        $page = @$this->params["page"];
        if ($page=="") $page=1;
        $offset = ($page -1) * $pageSize;

        # build page menu
        $reccount = $model->db->count();

        //echo $_SESSION['sys_langcode']; die;
        $types = $model->db->orderby('name')->limit($pageSize,$offset)->getFieldsArray();
        foreach ($types as $type)
        {
            $this->tpl->assign('editLink', $this->url->action('edit'));
            $this->tpl->assign('listMember', $this->url->action('index', 'MemberCP', array('type' => $type['id'])));
            $this->tpl->insert_loop('main.type', 'type', $type);
        }

        $this->tpl->assign("PAGE", Helper::pagging($page, $pageSize, $reccount));

        if ($type) $this->tpl->parse("main.button");

        $this->tpl->assign('addLink', $this->url->action('create'));
        $this->tpl->assign('deleteLink', $this->url->action('delete'));
        return $this->view();
    }

    function createAction()
    {
        $this->setView('edit');
        # form action
        $this->tpl->assign("form_action", $this->url->action('create'));
        $this->tpl->assign("listLink", $this->url->action('list'));
        return $this->view();
    }

    function createPost(Models_MemberType $model)
    {
        if ($model->Insert())
            $this->url->redirectAction('index');
        else
            $this->showError('Query error', $this->model->error);
    }

    function editAction()
    {
        $key = @$this->params["key"]; //key parameter
        $model = new Models_MemberType($key);
        $this->tpl->assign('listLink', $this->url->action('list'));
        return $this->view($model);
    }

    function editPost(Models_MemberType $model)
    {
        if ($model->Update()){
            $this->url->redirectAction('index');
        } else {
            $this->showError('Query error', $this->model->error);
        }
    }

    function deleteAjax()
    {
        $model = new Models_MemberType();
        $ids = $this->params['listid'];
        if (strpos($ids, ',') !== false)
        {
            $ids = explode(',', $ids);
            foreach ($ids as $id)
                if ($id != '')
                    if(!$model->db->where('id', $id)->Delete())
                        return json_encode(array('success' => false, 'msg' => $model->error));
        }
        elseif ($ids != '')
        {
            if(!$model->db->where('id', $ids)->Delete())
                return json_encode(array('success' => false, 'msg' => $model->error));
        }
        return json_encode(array('success' => true));
    }
}