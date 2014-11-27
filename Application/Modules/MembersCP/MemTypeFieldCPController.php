<?php
/**
 * @author phongbui
 */
class MemTypeFieldCPController extends Controller
{
    private $inputType = array('textbox' => 'Textbox','checkbox' => 'Checkbox','combobox' => 'Combobox','textarea' => 'Textarea','selecbox'=>'Selectbox');
    private $fieldType = array('int' => 'Int','float' => 'Float','string' => 'String','text' => 'Text','table'=>'Table','array'=>'Array');
    function __init()
    {
        $this->checkPermission();
        $this->loadTemplate('Metronic');
    }

    function indexAction()
    {
        $model = new Models_MemTypeField();
        $modelType = new Models_MemberType();
        $pageSize = 20;
        $where = "";
        # page navigation
        $page = @$this->params["page"];
        if ($page=="") $page=1;
        $offset = ($page -1) * $pageSize;

        if(isset($this->params['type']) && !empty($this->params['type']))
            $model->db->where('memtype_id', $this->params['type']);
        if(isset($this->params['search-text']) && !empty($this->params['search-text']))
        {
            $key = $this->params['search-text'];
            $model->db->where("field_name Like '%$key%' OR field_code Like '%$key%'");
        }
        # build page menu
        $reccount = $model->db->count();

        //echo $_SESSION['sys_langcode']; die;
        $fields = $model->db->orderby('orderno')->limit($pageSize,$offset)->getFieldsArray();
        foreach ($fields as $f)
        {
            $this->tpl->assign('editLink', $this->url->action('edit',array('id'=>$f['id'],'type' => @$this->params['type'])));
            $this->tpl->insert_loop('main.field', 'field', $f);
        }

        $this->tpl->assign("PAGE", Helper::pagging($page, $pageSize, $reccount));

        if ($fields) $this->tpl->parse("main.button");

        //list memtype
        $this->tpl->assign('sidebarType', $this->html->renderAction('sidebarType'));

        $this->tpl->assign('createLink', $this->url->action('create', array('type' => @$this->params['type'])));
        $this->tpl->assign('deleteLink', $this->url->action('delete'));
        return $this->view();
    }

    function indexPost()
    {
        $this->url->redirectAction('index',$this->params);
    }

    function createAction()
    {
        $this->setView('edit');
        $model = new Models_MemberType();
        $types = $model->db->select('id,name')->orderby('name')->getcFieldsArray();
        $this->tpl->assign('cmbMemType', $this->html->genSelect('memtype_id', $types, @$this->params['type'], 'id', 'name'));
        $this->tpl->assign('sidebarType', $this->html->renderAction('sidebarType'));
        $this->tpl->assign('cmbFieldType', $this->html->genSelect('field_type', $this->fieldType));
        $this->tpl->assign('cmbInputType', $this->html->genSelect('input_type', $this->inputType));
        $this->tpl->assign("form_action", $this->url->action('create',array('type'=>@$this->params['type'])));
        $this->tpl->assign("listLink", $this->url->action('index',array('type'=>@$this->params['type'])));
        $this->tpl->parse('main.saveAdd');
        return $this->view();
    }

    function createPost(Models_MemTypeField $model)
    {
        if ($model->Insert())
        {
            if(!isset($_POST['save_add']))
                $this->url->redirectAction('index',array('type'=>@$this->params['type']));
        }
        else
            $this->showError('Query error', $this->model->error);
    }

    function editAction()
    {
        $id = @$this->params["id"]; //key parameter
        $model = new Models_MemTypeField($id);
        $this->tpl->assign('listLink', $this->url->action('index',array('type'=>@$this->params['type'])));
        if($model->field_type == 'array' || $model->field_type == 'table')
        {
            if($model->field_type == 'array')
                $this->tpl->assign('valDefault','Nhập vào đoạn text có dạng: key1 : value1 | key2 : value2 | ...');
            if($model->field_type == 'table')
                $this->tpl->assign('valDefault','Nhập vào đoạn text có dạng: table : table_name | value_field : name_field | display_field : name_field');
            $this->tpl->assign('view',1);
        }
        $modelType = new Models_MemberType();
        $types = $modelType->db->select('id,name')->orderby('name')->getcFieldsArray();
        $this->tpl->assign('cmbMemType', $this->html->genSelect('memtype_id', $types, $model->memtype_id, 'id', 'name'));
        $this->tpl->assign('cmbFieldType', $this->html->genSelect('field_type', $this->fieldType, $model->field_type));
        $this->tpl->assign('cmbInputType', $this->html->genSelect('input_type', $this->inputType, $model->input_type));
        $this->tpl->assign('sidebarType', $this->html->renderAction('sidebarType'));
        return $this->view($model);
    }

    function editPost(Models_MemTypeField $model)
    {
        if($model->field_type != "table" && $model->field_type != "array")
            $model->field_type_value = '';
        if ($model->Update()){
            $this->url->redirectAction('index',array('type'=>@$this->params['type']));
        } else {
            $this->showError('Query error', $this->model->error);
        }
    }

    function deleteAjax()
    {
        $model = new Models_MemTypeField();
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

    /**
     * sidebar memtype
     */
    public function sidebarTypeAction()
    {
        $model = new Models_MemberType();
        $type = $model->db->getFieldsArray();
        foreach($type as $t)
        {
            $this->tpl->insert_loop('main.type', 'type', $t);
        }
        $this->unloadLayout();
        return $this->view();
    }
}