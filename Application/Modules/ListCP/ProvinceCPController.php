<?php
/**
 * Quản lý danh sách tỉnh thành
 * @author phongbui
 */
class ProvinceCPController extends Controller
{
    function __init()
    {
        $this->checkPermission();
        $this->loadTemplate('flatadmin');
        $this->loadLayout('index');
    }

    public function indexAction()
    {
        $this->tpl->assign('linkDel',$this->url->action('delete'));
        $this->tpl->parse('main.button');
        return $this->view();
    }

    public function dataTableAjax()
    {
        $models = new Models_ListProvince();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        if(isset($this->params['sSearch']))
            $models->db->like('name', $this->params['sSearch']);
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $models->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        $totalRow = $models->db->count()?$models->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $models->db->limit($pageSize, $offset)->getFieldsArray();
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }

    function createAjax()
    {
        $this->setView('edit');
        $model = new Models_ListProvince();
        $maxOrder = Models_ListProvince::getMaxOrder();
        $model->order_no = $maxOrder+1;
        # form action
        $this->tpl->assign("form_action", $this->url->action('createPost'));
        $this->tpl->assign("listLink", $this->url->action('list'));

        $this->unloadLayout();
        return $this->view($model);
    }

    function createPostAjax(Models_ListProvince $model)
    {
        $model->lang_code = @$_SESSION['sys_langcode'];
        if ($model->Insert())
            return json_encode(array('success' => true, 'msg' => "Cập nhật thành công!",'dataTable'=>'tableProvince'));
        else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
    }

    function editAjax()
    {
        $key = @$this->params["id"]; //key parameter
        $model = new Models_ListProvince($key);
        $this->tpl->assign('listLink', $this->url->action('list'));
        $this->tpl->assign("form_action", $this->url->action('editPost'));
        $this->unloadLayout();
        return $this->view($model);
    }

    public function editPostAjax(Models_ListProvince $model)
    {
        if ($model->Update())
            return json_encode(array('success' => true, 'msg' => "Cập nhật thành công!",'dataTable'=>'tableProvince'));
        else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
    }

    function deleteAjax()
    {
        $model = new Models_ListProvince();
        $ids = $this->params['listid'];
        if (strpos($ids, ',') !== false)
        {
            $ids = explode(',', $ids);
            foreach ($ids as $id)
                if ($id != '')
                    if(!$model->Delete("id='$id'"))
                        return json_encode(array('success' => false, 'msg' => $model->error));
        }
        elseif ($ids != '')
        {
            if(!$model->Delete("id='$ids'"))
                return json_encode(array('success' => false, 'msg' => $model->error));
        }
        return json_encode(array('success' => true));
    }
}