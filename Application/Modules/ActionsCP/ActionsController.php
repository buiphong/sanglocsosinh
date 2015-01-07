<?php
class ActionsController extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
		$this->loadLayout('index');
	}
	
	function listAction()
	{
        $this->tpl->assign('groupid', @$this->params['groupid']);
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign("backLink", $this->url->action('list', 'ActionGroups', 'actions'));
		return $this->view();
	}

    public function listAjax()
    {
        $pageSize = @$this->params["iDisplayLength"];
        $offset = @$this->params["iDisplayStart"];

        $model = new Models_Action();
        if (!empty($this->params['groupid']))
            $model->db->where('groupid', $this->params['groupid']);
        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        else
            $model->db->orderby('name');
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->limit($pageSize,$offset)->getFieldsArray();
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
	
	/**
	 * Thêm mới chức năng
	 */
	function createAjax()
	{
		$this->setView('edit');
		# form action
		$this->tpl->assign("form_action", $this->url->action('create'));
		//Nhóm chức năng
		$modelAGroup = new Models_ActionGroup();
		$this->tpl->assign('groupid', $this->html->genSelect('groupid',
				$modelAGroup->db->select('id,name')->orderby('name')->getFieldsArray(), @$this->params['groupid'], 'id', 'name',array('class' => 'chosen-select')));
		//Status
		$this->tpl->assign('status', $this->html->genCheckbox('status', 1));
		$this->tpl->assign('frmAction', $this->url->action('createPost', array('groupid' => @$this->params['groupid'])));
		$this->tpl->assign('group', $modelAGroup->db->select('name')->where('id', @$this->params['groupid'])->getField());
        $this->unloadLayout();
		return $this->view();
	}
	
	function createPostAjax(Models_Action $model)
	{
		if ($model->Insert()){
			return json_encode(array('success' => true, 'dataTable' => 'tableAction'));
		} else {
            return json_encode(array('success' => false, 'msg' => $model->error));
		}
	}
	
	function editAjax()
	{
		$key = @$this->params["id"];
		$model = new Models_Action($key);
		//Nhóm chức năng
		$modelAGroup = new Models_ActionGroup();
		$this->tpl->assign('groupid', $this->html->genSelect('groupid', 
				$modelAGroup->db->select('id,name')->orderby('name')->getAll(), $model->groupid, 'id', 'name',array('class' => 'chosen-select')));
		//Status
		$this->tpl->assign('status', $this->html->genCheckbox('status', 1, $model->status));
		
		$this->tpl->assign("frmAction", $this->url->action('editPost'));
		$this->tpl->assign('backLink', $this->url->action('list', 'Actions', array('groupid' => $model->groupid)));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('group', $modelAGroup->db->select('name')->where('id', $model->groupid)->getOne());
		$this->tpl->assign('groupLink', $this->url->action('list', 'ActionGroups'));
		$this->tpl->assign('action',$model->name);
        $this->unloadLayout();
		return $this->view($model);
	}
	
	function editPostAjax(Models_Action $model)
	{
		if ($model->Update()){
            return json_encode(array('success' => true, 'dataTable' => 'tableAction'));
		} else {
            return json_encode(array('success' => false, 'msg' => $model->error));
		}
	}
	
	function deleteAjax()
	{
		$model = new Models_Action();
		$ids = $this->params['id'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->Delete("id=$id"))
					return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		return json_encode(array('success' => true, 'dataTable' => 'tableAction'));
	}
	
	/*
	 Show action group by actiongroups
	*/
	function showCheckboxes($name, $statusValues, $disabled=0, $className="treeview-red")
	{
		$modelGroup = new Models_ActionGroup();
		$modelAction = new Models_Action();
		$arrGroups = $modelGroup->db->select('id, name')->getAll();
		$treeId = "actions_tree";
		$str = "<ul id='".$treeId."' class='$className'>";
		foreach ($arrGroups as $group)
		{
			$arrAction = $modelAction->db->select('id,name')->where('groupid', $group['id'])->getAll();
			if (!$arrAction)
				echo $modelAction->db->error;
			//Gen checkbox group
			$str.="<li><span style='font-weight:bold'>".$group["name"]."</span>\n";
			//$str.="<li><"."input class='chksysactions' type='checkbox' name='$name"."[]' value=''/><span style='font-weight:normal'>".$group["name"]."</span>\n";
			//Gen multicheckbox childrent
			$str.="<ul>".$this->html->genMultiCheckboxesFromRs($name, $arrAction, $statusValues,$disabled, 'id', 'name')."</ul>\n";
			$str.="</li>\n";
		}
		$str.= "</ul>\n";
		$str.= $this->html->treeJS($treeId);
		return $str;
	}//end
	
	function returnActionAction()
	{
        $this->loadModule('MenuCP');
		$menu = @$this->params["id"];
		$menuModel = new Models_SystemMenu($menu);
		if ($menuModel!=""){
			$_SESSION["system_menuid"] = $menu;
		} else {
			$menu = @$_SESSION["system_menuid"];
		}
		if ($menu==""){
			$menu = "-1";
			@$_SESSION["system_menuid"] = "-1";
		}
		if ($menu=="-1") header("Location: index.php");
		
		if (!empty($menuModel->externallink))
			header("Location: " . $menuModel->externallink);
		$action = $menuModel->actionid;
		$action = new Models_Action($action);
		if ($action->controller != ""){
			//redirect action
			$this->url->redirectAction($action->action, $action->controller, $action->module);
		}
	}
}