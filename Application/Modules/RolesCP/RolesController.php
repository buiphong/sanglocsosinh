<?php
class RolesController extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
        $this->loadModule(array('ActionsCP', 'MenuCP'));
	}
	
	function listAction()
	{
		$model = new Models_Roles();
		$pageSize = 20;
		$page = @$this->params['page'];
		$page = 1;
		$offset = ($page - 1) * $pageSize;
		$search = "";
		$roles = $model->db->select('id,name,description')->limit($pageSize, $offset)->getFieldsArray();	
		$totalRows = $model->Count($search);
		if(!empty($roles))
		{
			foreach ($roles as $role)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id' => $role['id'])));
				$this->tpl->insert_loop('main.role', 'role', $role);
			}
		}
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		if ($totalRows > 0)
			$this->tpl->parse('main.button');
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		return $this->view();
	}
	
	function createAction()
	{
        $this->setView('edit');
        $checkboxes = "status,newsRole";
        $model = new Models_Roles();
        $quote="'";
        $a = @$this->params["a"];

        # for checkboxes
        $checkArr = explode(",", $checkboxes);
        foreach($checkArr as $v) $this->tpl->assign($v, $this->html->genCheckbox($v, "1", @$fieldList[$v]));

        # form action
        $this->tpl->assign("form_action", $this->url->getUrlAction(array('create', 'Roles', 'RolesCP')));

        # actions
        //$A = new ActionsController($this->conn);
        $this->tpl->assign("actions", $this->showCheckboxes("actions", ""));
        $this->tpl->assign('listLink', $this->url->action('list'));

        $arrUMenu = explode(',', $model->menu);
        $treeMenu = $this->getTreeMenu('0', $arrUMenu,'menu[]');
        $this->tpl->assign('sysmenu', $treeMenu);
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
        $this->tpl->assign('listRole', $this->url->action('list'));
        $this->tpl->assign('title', 'Thêm mới');
        return $this->view();
	}
	function createPost(Models_Roles $model)
	{
		if (!empty($model->actions))
			$model->actions = implode(',', $model->actions);
		
		if (!empty($model->menu))
			$model->menu = implode(',', $model->menu);
			
		if ($model->Insert()){
			$this->url->redirectAction('list');
		} else {
			$this->showError('Mysql Error', $model->error);
		}
	}
	
	
	function editAction()
	{
		$key = @$this->params["id"]; //key parameter
		$model = new Models_Roles($key);
	
		# action
		$this->tpl->assign("form_action", $this->url->action('edit'));
		
		//$A = new ActionsController();
		$this->tpl->assign("actions", $this->showCheckboxes("actions",  $model->actions));
        //$this->tpl->assign("actions", $B->showCheckboxe("actions",  $model->actions));
		$this->tpl->assign('listLink', $this->url->action('list'));
		//lấy danh sách menu
		$arrUMenu = explode(',', $model->menu);
		$treeMenu = $this->getTreeMenu('0', $arrUMenu,'menu[]');
		$this->tpl->assign('sysmenu', $treeMenu);
		$this->tpl->assign('status', $this->html->genCheckbox('status', "1", $model->status));
		$this->tpl->assign('newsRole', $this->html->genCheckbox('newsRole', "1", @$model->newsRole));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
        $this->tpl->assign('listRole', $this->url->action('list'));
        $this->tpl->assign('title', $model->name);
		return $this->view($model);
	}
	
	function editPost(Models_Roles $model)
	{
		if (!empty($model->actions))
			$model->actions = implode(',', $model->actions);
		if (!empty($model->menu))
			$model->menu = implode(',', $model->menu);
		if ($model->Update()){
			$this->url->redirectAction('list');
		}
		else
		{
			die("Can not update data: ".$model->error);
		}
	}
	
	function deleteAjax()
	{
		$model = new Models_Roles();
		$this->checkPermission();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
			{
                if ($id != '')
    				if(!$model->db->where('id', $id)->Delete())
    				{
    					return json_encode(array('success' => false, 'msg' => $model->db->error));
    					break;
    				}
            }
		}
		elseif ($ids != '')
		{
			if(!$model->db->where('id', $ids)->Delete())
				return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		return json_encode(array('success' => true));
	}
	
	function getTreeMenu($parentId = '0', $arrValue = array(), $name = 'sysmenuid[]') //sysmenuid
	{
		$model = new Models_SystemMenu();
		//Lấy danh sách menu hệ thống theo dạng cây
		$arrMenu = $model->db->select('id,title,languageid')->where('status', 1)->where('parentid', $parentId)->getAll();
		$htmlMenu = '';
		if (!empty($arrMenu))
		{
			if ($parentId == '0')
				$htmlMenu = '<ul id="menuTreeview" class="menuTreeview">';
			else
				$htmlMenu = '<ul class="menuTreeview">';
			foreach ($arrMenu as $menu)
			{
				if (in_array($menu['id'], $arrValue))
					$htmlMenu .= '<li><input checked="checked" name="'.$name.'" class="chksysmenu" type="checkbox" value="'.$menu['id'].'"/>' . $menu['title'];
				else
					$htmlMenu .= '<li><input name="'.$name.'" class="chksysmenu" type="checkbox" value="'.$menu['id'].'"/>' . $menu['title'];
					
				$htmlMenu .= $this->getTreeMenu($menu['id'], $arrValue, $name);
                $htmlMenu .= '</li>';
			}
			$htmlMenu .= '</ul>';
		}
		return $htmlMenu;
	}

    function showCheckboxes($name, $statusValues, $disabled=0, $className="treeview-red")
    {
        $modelGroup = new Models_ActionGroup();
        $modelAction = new Models_Action();
        $arrGroups = $modelGroup->db->select('id, name')->getAll();
        $treeId = "actions_tree";
        $arrValues = explode(',', $statusValues);
        $str = "<ul id='".$treeId."' class='$className'>";
        foreach ($arrGroups as $group)
        {
            $arrAction = $modelAction->db->select('id,name')->where('groupid', $group['id'])->getFieldsArray();
            if (!$arrAction)
                echo $modelAction->db->error;
            //Gen checkbox group
            $str.="<li><"."input class='chksysactions' type='checkbox' name='$name"."[]' value='".$group['id']."'";
            if(in_array($group['id'], $arrValues))
                $str .= " checked='checked'";
            $str .= "/>".$group["name"]."\n";
            //Gen multicheckbox childrent
            $str.="<ul>".$this->html->genMultiCheckboxesFromRs($name, $arrAction, $statusValues,$disabled, 'id', 'name')."</ul>\n";
            $str.="</li>\n";
        }
        $str.= "</ul>\n";
        $str.= $this->html->treeJS($treeId);
        return $str;
    }//end
}