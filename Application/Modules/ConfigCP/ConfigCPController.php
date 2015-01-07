<?php
/**
 * @author buiphong
 *
 */
class ConfigCPController extends Controller
{

	public function __init()
	{
		$this->loadTemplate('Metronic');
		$this->checkPermission();
	}
	
	public function indexAction()
	{
        $modelGroup = new Models_ConfigGroup();
        $model = new Models_Config();
        $mValue = new Models_ConfigValue();
		//Lấy danh sách cấu hình
        $groups = $modelGroup->db->orderby('order_no')->getFieldsArray();
        $i = 1;
        foreach($groups as $g)
        {
            //Get group's config
            $model->db->where('config.group_id', $g['id']);
            $configs = $model->db->orderby('title')->getFieldsArray();
            foreach($configs as $conf)
            {
                //Get value
                $conf['value'] = $mValue->getConfValue($conf['code']);
                $this->tpl->insert_loop('main.group.conf', 'conf', $conf);
            }
            if($i == 2)
            {
                $this->tpl->parse('main.group.clearBoth');
                $i=1;
            }
            else
                $i++;
            $this->tpl->insert_loop('main.group', 'group', $g);
        }
		$this->tpl->assign('createLink', $this->url->action('create', array('groupid' => @$this->params['groupid'])));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('groupLink', $this->url->action('index', 'ConfigGroupCP'));
		$this->tpl->assign('group', $modelGroup->name);
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
        $this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}

    public function changeConfigAjax()
    {
        $model = new Models_ConfigValue();
        $name = @$this->params['name'];
        $value = @$this->params['value'];
        if (!empty($name)) {
            if($model->updateValue($name, $value))
                return json_encode(array('success' => true, 'msg' => 'successfully'));
            else
                return json_encode(array('success' => false, 'msg' => $model->error));
        }
        return json_encode(array('success' => false, 'msg' => 'Giá trị chưa đúng'));
    }
	
	public function listAction()
	{
		
		return $this->view();
	}
	
	/**
	 * Thêm mới slide
	 */
	public function createAction()
	{
		$this->setView('edit');
		$this->tpl->assign('form_action', $this->url->action('create'));
		$this->tpl->assign('groupid', $this->params['groupid']);
		
		$modelGroup = new Models_ConfigGroup($this->params['groupid']);
		$this->tpl->assign('group', $modelGroup->name);
		$this->tpl->assign('listLink', $this->url->action('index', array('groupid' => $this->params['groupid'])));
		$this->tpl->assign('groupLink', $this->url->action('index', 'ConfigGroupCP'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view();
	}
	
	public function createPost(Models_Config $model)
	{
        $model->lang_code = @$_SESSION['sys_langcode'];
		if($model->Insert())
			$this->url->redirectAction('index', array('groupid' => $model->group_id));
		else
			$this->showError('Mysql Error', $model->error);
	}
	
	/**
	 * Sửa thông tin danh mục
	 */
	public function editAction()
	{
		$model = new Models_Config($this->params['id']);
		$this->tpl->assign('groupid', $this->params['groupid']);
		$this->tpl->assign('listLink', $this->url->action('index', array('groupid' => $model->group_id)));
		$this->tpl->assign('groupLink', $this->url->action('index', 'ConfigGroupCP'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		
		$modelGroup = new Models_ConfigGroup($model->group_id);
		$this->tpl->assign('group', $modelGroup->name);
		
		$elementValue = "<textarea class='field' name='value' id='value' rows=5 cols=60>$model->value</textarea>";
		if($model->code == "config_logo")
		{
			$elementValue = "<input class='field' type='text' name='value' id='value' size=30 value='$model->value'>
					<input class='button' type='button' id='filePath' name='filePath' onclick='openFileBrowser(\"value\")' value='Quản lý file'/>";
		}
		$this->tpl->assign('elementValue',$elementValue);
		return $this->view($model);
	}
	
	public function editPost(Models_Config $model)
	{
        $model->lang_code = @$_SESSION['sys_langcode'];
		if($model->Update())
			$this->url->redirectAction('index', array('groupid' => $model->group_id));
		else
			$this->showError('Mysql Error', $model->error);
	}
	
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$model = new Models_Config();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
					if(!$model->Delete("id=$id"))
						return json_encode(array('success' => false, 'msg' => $model->error));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('index')));
	}

    public function testSendMailAjax()
    {
        $email = new VccMail();
        if($email->send($this->params['email'], 'Test mail', 'Bạn nhận được mail này vì đã thực hiện test gửi mail thành công trên hệ thống portal VC. Vui lòng không trả lời lại thư này.'))
            return json_encode(array('success' => true));
        else
            return json_encode(array('success' => false));
    }
}