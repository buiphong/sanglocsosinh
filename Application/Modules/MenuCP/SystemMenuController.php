<?php
class SystemMenuController extends Controller
{
	function __init()
	{
		//$this->checkPermission();
		$this->loadTemplate('Metronic');
		$this->loadLayout('index');
        $this->loadModule(array('UsersCP', 'ActionsCP'));
	}
	
	function listAction()
	{
		$modelMenu = new Models_SystemMenu();
		$modelAction = new Models_Action();
		$pagesize = 20;
		# anchor parent
		$parentid = @$this->params["parentid"];
        $parentName = '';
		
		if ($parentid=="")
		{
			$parentid = "0";
		}
		else
		{
			$parentName = $modelMenu->db->select('title')->where('id', $parentid)->getOne();			
		}
		
		$modelMenu->db->where('parentid', $parentid);

		//Menu type
		$title = "Menu hệ thống";
		if (!empty($this->params['type']))
		{
			$type = $this->params['type'];
			$modelMenu->db->where('type_id',$this->params['type']);
			$modelType = new Models_SystemMenuType();
			$title = $modelType->db->select('type_name')->where('id',$type)->getOne();
		}
		
		# page navigation
		$page = @$this->params["page"];
		if ($page=="") $page=1;
		$offset = ($page - 1) * $pagesize;

		# build page menu
		$reccount = $modelMenu->db->count();
		
		# main form
		$data = $modelMenu->db->orderby('orderno')->limit($pagesize, $offset)->getAll();
		$rownum = 0;
		foreach ($data as $row){
			if ($parentid=="0"){
				$row["title"] = "<a href=\"?type=".$row['type_id']."&parentid=".$row["id"]."\">".$row["title"]."</a>";
			}
			
			$row['actionid'] = $modelAction->db->select('name')->where('id', $row['actionid'])->getOne();
			# assign row
			$this->tpl->assign('systemmenu', $row);
			$this->tpl->assign('editLink', $this->url->action('edit', array('parentid' => $parentid, 'key' => $row['id'])));
			$this->tpl->assign('addLink', $this->url->action('create', array('parentid' => $parentid)));
			$this->tpl->assign('listLink', $this->url->action('list'));
			# now parse row
			$this->tpl->parse("main.systemmenu");
			$rownum++;
		}
		if($parentName)
		{
			$title = $parentName;
		}
		$this->tpl->assign('title',$title);
		$this->tpl->assign("PAGE", Helper::pagging($page, $pagesize, $reccount));
		
		# statistic
		$this->tpl->assign("TOTAL", number_format($reccount, 0, ".", ",")); 

		if ($data) $this->tpl->parse("main.button");

		$type = new Models_SystemMenuType(@$this->params['type']);
		$this->tpl->type = $type->type_name;
		$this->tpl->assign('addLink', $this->url->action('add', array('type' => @$this->params['type'], 'parentid' => @$this->params['parentid'])));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
        $this->tpl->assign('sysMenuLink', $this->url->action('list'));
		return $this->view();
	}
	
	function addAction()
	{
		$this->setView('edit');
		$model = new Models_SystemMenu();
		$modelAction = new Models_Action();
		$this->tpl->assign('actionid', $this->html->genSelect('actionid',
				$modelAction->db->select('name,id')->orderby('name')->getAll(),
				'','id','name', array('class' => 'chosen-select'), ' ', true));
		$this->tpl->assign('status', $this->html->genCheckbox('status', 1, 1));
		# form action
		$this->tpl->assign("form_action", $this->url->action('add', array('type' => @$this->params['type'], 'parentid' => @$this->params['parentid'])));
		$this->tpl->assign('listLink',$this->url->action('list'));
        if(isset($this->params['type']))
        {
		    $type = new Models_SystemMenuType($this->params['type']);
		    $this->tpl->assign('type',$type->type_name);
	    	$this->tpl->assign('typeid',@$this->params['type']);
        }
		return $this->view();
	}
	
	function addPost(Models_SystemMenu $model)
	{
		if (!empty($this->params['parentid']))
			$model->parentid = $this->params['parentid'];
		# Now update table
		if ($model->Insert()){
			$this->url->redirectAction('list', array('type'=>$model->type_id, 'parentid' => $model->parentid));
		} else {
			$this->showError('Mysql Error', $this->model->error);
		}
	}
	
	function editAction()
	{			
		# for menus
		$checkboxes = "status";
		$key = @$this->params["key"]; //key parameter		
		$modelMenu = new Models_SystemMenu($key);		
		$menuArr = array('actionid'=>'select name,id from actions');//menus in form (combo boxes)
		foreach($menuArr as $k=>$v){//generate menus
			$rs = $modelMenu->db->Execute($v);
			if ($rs){
				$this->tpl->assign($k, $this->html->genMenuRs($k, $rs, $modelMenu->$k,'',0, 'style="width: 315px;" class="field chosen-select"')); $rs->Close();
			}
		}

		$modelAction = new Models_Action();
		$this->tpl->assign('actionid', $this->html->genSelect('actionid',
				$modelAction->db->select('name,id')->orderby('name')->getAll(),
				$modelMenu->actionid,'id','name', array('class' => 'chosen-select'), ' ', true));
		# for checkboxes
		$checkArr = explode(",", $checkboxes);
		foreach($checkArr as $v) $this->tpl->assign($v, $this->html->genCheckbox($v, "1", $modelMenu->$v));
		
		# action
		$this->tpl->assign("form_action", $this->url->action('edit', array(
									'parentid' => $this->params['parentid'],
									'type_id' => @$this->params['type'])));

		# parse and out
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('typeid',$modelMenu->type_id);
		return $this->view($modelMenu);
	}
	
	function editPost(Models_SystemMenu $model)
	{
		if (empty($model->status))
			$model->status = 0;
		if (empty($model->type_id))
			$model->type_id = 0;
		
		if ($model->Update()){
			$this->url->redirectAction('list',array('type' => $model->type_id, 'parentid' => $this->params['parentid']));
		} else {
			die("Can not update data: ".$this->model->error);
		}
	}
	
	function deleteAjax()
	{
		$model = new Models_SystemMenu();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
                    if(!$model->Delete("id='$id'"))
                        return json_encode(array('success' => false, 'msg' => $model->error));
                    else
                    {
                        //Xoá menu con

                    }
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id='$ids'"))
				return json_encode(array('success' => false, 'msg' => $model->error));
		}
		return json_encode(array('success' => true));
	}
	
	function genMenuAction()
	{
		# find selected menu
		$menu = @$_SESSION["system_menuid"];
		$modelMenu = new Models_SystemMenu($menu);
		$modelRoles = new Models_Roles();
		$parent = $modelMenu->parentid;
		if (intval($parent)>0) $menu = $parent;
		
		$model_user = new Models_User();
		$user = $model_user->db->where('username', $_SESSION['pt_control_panel']['system_username'])
							->where('password', $_SESSION['pt_control_panel']['password'])->getFields();
            
		//Lấy thông tin người dùng, kiểm tra danh sách menu
		$roles = explode(',', $user['roles']);
		$userMenu = '';
		foreach ($roles as $roles)
		{
			$userMenu .= ',' . $modelRoles->db->select('menu')->where('id', $roles)->getField();
		}
		//Get user menu
		$umenuModel = new Models_UserMenu();
		$umenu = $umenuModel->db->select('menuid')->where('userid', $_SESSION['pt_control_panel']['system_userid'])->getField();
		if ($umenu)
			$userMenu .= ',' . $umenu;
		$items = $modelMenu->db->select('id,title,externallink,icon_class')
							->where("parentid='0' and instr('$userMenu', id) > 0 and status=1")
							->orderby('orderno')->getFieldsArray();
		$num = 0;
		if ($items)
		{
			foreach ($items as $item)
			{
				$item['unread'] = '';
				$item["link"] = $this->url->getUrlAction(array('returnAction', 'Actions', 'ActionsCP', array('id' => $item['id'])));
				$item["class"] = "";
				if ($menu=="" && $num==0){
					$item["class"] = "current active";
				}//
				if ($item["id"]==$menu){
					$item["class"] = "current active";
				}
				
				$item['dataToggle'] = '';
				$item['caret'] = '';
				
				# sub
				$subitems = $modelMenu->db->select('id,title')
										->where("parentid='".$item["id"]."' and instr('$userMenu', id) > 0 and status=1")
										->orderby('orderno')
										->getFieldsArray();
				if ($subitems)
				{
					foreach ($subitems as $subitem)
					{
						$subitem["link"] = $this->url->getUrlAction(array('returnAction', 'Actions', 'ActionsCP', array('id' => $subitem['id'])));
						$this->tpl->insert_loop("main.item.sub.subitem", 'subitem', $subitem);
					}
					$this->tpl->parse('main.item.sub');
					$item['class'] .= ' dropdown-toggle';
					$item['dataToggle'] = 'dropdown';
					$item['caret'] = '<span class="caret"></span>';
				}
				$this->tpl->insert_loop('main.item', 'item', $item);
				$num++;
			}
		}
		$this->unloadLayout();
		return $this->view();
	}
	
	/**
	 * reload menu
	 */
	function reloadMenuAjax()
	{
		return json_encode(array('success' => true, 'html' => $this->genMenu()));
	}
	
	/**
	 * Right menu admin
	 */
	public function rightMenuAction()
	{
		$model = new Models_SystemMenu();
		//lấy toàn bộ menu 
		$menus = $model->getMenuMultiLevel(0, array('type_id' => 2));
		foreach ($menus as $menu)
		{
			if (!empty($menu['childs']))
			{
				foreach ($menu['childs'] as $child)
				{
					$child["link"] = $this->url->getUrlAction(array('returnAction', 'Actions', 'ActionsCP', array('id' => $child['id'])));
					$this->tpl->insert_loop('main.menu.child.menu', 'menu', $child);
				}
				$this->tpl->parse('main.menu.child');
			}
			$this->tpl->insert_loop('main.menu', 'menu', $menu);
		}
		$this->unloadLayout();
		return $this->view();
	}
}//end of class
?>