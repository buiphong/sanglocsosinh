<?php
class UsersController extends Controller
{
    public $userId;

	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('Metronic');
        $this->loadModule('MenuCP');
        if(isset($_SESSION['pt_control_panel']['system_userid']))
            $this->userId = $_SESSION['pt_control_panel']['system_userid'];
	}
	
	function listAction()
	{
		$flds = "id,username,password,fullname,email,description,departmentid,position,roles,moreactions";
		$pagesize = 20;
		$filter = "";
		$order = "fullname";
		$ordertype = "asc";
		$menuArr = array("typeid"=>"select typename, typeid from usertypes where typeid='#value#'");
		$multiselectArr = array('roles'=>'select name,id from roles','moreactions'=>'select name,id from actions','departmentid'=>'select name,id from f_departments');
		
		$modelUser = new Models_User();
		# for action: search
		$a = @$this->params["a"];
		$search = "";
		$txtsearch = @$this->params["txt_search"];
		if ($txtsearch<>""){
			$fldArr = explode(",", $flds);
			$search="";
			for ($i=0;$i<count($fldArr);$i++){
				$search.="(".$fldArr[$i]." LIKE '%".$txtsearch."%') OR ";
			}
			if (strlen($search)>3) $search = substr($search, 0, strlen($search)-3);
            $this->tpl->assign('textSearch', $txtsearch);
		}

		$where = "";
		if ($search<>"" || $filter<>""){
			if ($search<>"") {
				$where.="(".$search.")";
				if ($filter<>"") $where.=" AND (".$filter.")";
			} else {
				if ($filter<>"") $where.=$filter;
			}
		}//end if where
		
		$modelUser->db->where($where);

		# page navigation
		$page = @$this->params["page"];
		if ($page=="") $page=1;
		$offset = ($page - 1) * $pagesize;
		
		$reccount = $modelUser->db->count();
		
		# main form
		$arrUser = $modelUser->db->orderby('fullname')->limit($pagesize, $offset)->getFieldsArray();
		if(!empty($arrUser))
		{
			$rownum = 0;
			foreach ($arrUser as $user)
			{
				# check
				$check = "";
				if (strtolower($user["username"])=="admin") $check = " disabled";
				$this->tpl->assign("disabled", $check);
				$this->tpl->assign('editLink', $this->url->action('edit'));
				# now parse row
				$this->tpl->insert_loop('main.users','users', $user);
				$rownum++;
			}
		}
		$this->tpl->assign("PAGE", Helper::pagging($page, $pagesize, $reccount));
		$this->tpl->assign('frmAction', $this->url->action('list'));
		if ($reccount > 0) $this->tpl->parse("main.button");
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('createLink', $this->url->action('create'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		return $this->view();		
	}
	
	function listPost()
	{
		$this->url->redirectAction('list', 'Users', 'users', $this->params);
	}

    function createAjax()
    {
        $ids = $this->params['roleid'];
        $idu = $this->params['checkid'];
        $modelRoles = new Models_Roles();
        $arrResult = $modelRoles->db->select('actions')->where_in("id",$ids)->getFieldArray();
        $A = new ActionsController();
        $str = $arrResult[0];
        $str = $str.$idu;
        $str = $A->showCheckboxes("moreactions", $str, 0, "treeview-red treeview", 'id', 'name');
        return json_encode(array("success"=>"Xuất hiện str", "msg"=>$str));
    }
	function createAction()
    {
		$this->setView('edit');
		$multiselectArr = array(
				'roles'=>'select name,id from roles'
		);
		$modelUser = new Models_UserMenu();
		# multi selections
		foreach($multiselectArr as $k=>$sql)
		{
			$dval = "";
			$rs = $modelUser->db->Execute($sql);
			if ($rs) $this->tpl->assign($k, $this->html->genMultiCheckboxes($k, $rs, $dval));
		} //end for multi selections
		
		# for checkboxes
		$this->tpl->assign('status', $this->html->genCheckbox('status', "1", ''));

		//Thêm quyền
		$A = new ActionsController();
        $this->tpl->assign("moreactions", $A->showCheckboxes("moreactions", "", 0, "treeview-red", 'id', 'name'));
		//Lấy danh sách menu hệ thống theo dạng cây
		$treeMenu = $this->getTreeMenu('0','', 'menuid[]');
		$this->tpl->assign('menuid', $treeMenu);
		$this->tpl->assign("readonly", '');
		$this->tpl->assign("listLink", $this->url->action('list'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
        $this->tpl->assign('title', 'Thêm mới');
		return $this->view();
	}

    function createPost(Models_User $model)
    {
        if(!empty($this->params['new_password']))
            $model->password = md5($this->params['new_password']);
        if (!empty($model->moreactions))
            $model->moreactions = implode(',', $model->moreactions);
        if (!empty($model->roles))
            $model->roles = implode(',', $model->roles);
        if (!empty($model->menuid))
            $model->menuid = implode(',', $model->menuid);
        $arrMenu = $model->db->select('id')->where('username',$model->username)->getFieldsArray();
        $count = count($arrMenu);
        if(empty($this->params['new_password']))
        {
            $pass = String::randomString(8);
            $model->password = md5($pass);
        }
        if($count >= 1)
        {
            $this->showError("Username đã tồn tại!", $model->error);
        }
        else
        {
            if ($model->Insert()){
                if(empty($this->params['new_password']))
                {
                    $mail = new VccMail();
                    if(!$mail->send($model->email,"Chúc mừng bạn đã tạo tài khoản thành công!","Với tên Username:'".$model->username."' và Password: '".$pass."'"))
                        $mail->getErrorMessage();
                }
                $this->url->redirectAction('list');
            }
            else {
                $this->showError('Mysql Error', $model->error);
            }
        }
    }
	
	function editAction()
	{
		$A = new ActionsController();
		$modelUserMenu = new Models_UserMenu();
		$flds = "username,fullname,email,status,description,departmentid,position,roles,moreactions,staffid"; //fields being searched separated by comma

		$multiselectArr = array('roles'=>'select name, id from roles','moreactions'=>'select name,id from actions','departmentid'=>'select name,id from f_departments');
		$key = @$this->params["key"]; //key parameter
		# show form to enter data
		$modelUser = new Models_User($key);
		
		# multi selections
		foreach($multiselectArr as $k=>$sql){
			$rs = $modelUser->db->Execute($sql);
			if ($rs) $this->tpl->assign($k, $this->html->genMultiCheckboxes($k, $rs, $modelUser->$k));
		}  //end for multi selections
		
		
		# action
		$this->tpl->assign("form_action", $this->url->action('edit'));

		$this->tpl->assign("readonly", 'readonly="readonly"');
		# special
		$this->tpl->assign("moreactions", $A->showCheckboxes("moreactions", $modelUser->moreactions, 0, "treeview-red", 'id', 'name'));
		
		//user menu
		//$umenu = $modelUserMenu->db->select('menuid')->where('userid', $key)->getField();
        $umenu = $modelUser->db->select('menuid')->where("username", $modelUser->username)->getField();
		$arrUMenu = explode(',', $umenu);
		//Lấy danh sách menu hệ thống theo dạng cây
		$treeMenu = $this->getTreeMenu('0', $arrUMenu, 'menuid[]');
		$this->tpl->assign('listLink', $this->url->action('list'));
		$this->tpl->assign('menuid', $treeMenu);
		$this->tpl->assign('status', $this->html->genCheckbox('status', "1", $modelUser->status));
		$this->tpl->assign('newsRole', $this->html->genCheckbox('newsRole', "1", $modelUser->newsRole));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		return $this->view($modelUser);
	}

    function editPost(Models_User $model)
    {
        //$model = new Models_User();
        if(!empty($this->params['new_password']))
            $model->password = md5($this->params['new_password']);
        if(!empty($model->moreactions))
        {
            $model->moreactions = implode(',', $model->moreactions);
        }
        if(!empty($model->menuid))
        {
            $model->menuid = implode(',', $model->menuid);
        }
        if(!empty($model->roles))
        {
            $model->roles = implode(',', $model->roles);
        }
        if($model->Update())
        {
            $this->url->redirectAction('list');
        }
        else
        {
            $this->showError("Không thể update!", $model->error);
        }
    }
	
	function deleteAjax()
	{
        $model = new Models_User();
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
	
	/**
	 * Thay đổi mật khẩu
	 */
	function changePassAction()
	{
		$this->tpl->assign('username', $_SESSION['pt_control_panel']["system_username"]);
		$this->tpl->assign('msg', $this->params['msg']);
		if ($this->params['status'] == 'success')
			$this->tpl->assign('color', 'green');
		else
			$this->tpl->assign('color', 'red');
		
		return $this->view();
	}
	
	function changePassAjax()
	{
		$uid = $_SESSION['pt_control_panel']["system_userid"];
		if ($this->params["x_password1"]<>$this->params["x_password2"] || $this->params['x_password1'] == ''){
			return json_encode(array('success' => false, 'msg' => 'Xác nhận mật khẩu mới chưa chính xác, hoặc mật khẩu bị trống!'));
		}
        else
        {
            $model = new Models_User();
			$y_password = $model->db->select('password')->where('id', $uid)->getField();
			if ($y_password<>md5($this->params["x_password"])){
                return json_encode(array('success' => false, 'msg' => 'Mật khẩu cũ không chính xác!'));
			} else {
				if (!$model->db->where('id', $uid)->update(array('password' => md5($this->params["x_password1"])))){
                    return json_encode(array('success' => false, 'msg' => 'Không thể thay đổi mật khẩu!'));
				} else {
                    return json_encode(array('success' => true, 'msg' => 'Thay đổi mật khẩu thành công!'));
				}
			}
		}
		$this->url->redirectAction(array('changePass', 'Users', 'users', array('msg' => $msg, 'status' => $status)));
	}
	
	function getTreeMenu($parentId = '0', $arrValue = array(), $name = 'menuid[]')
	{
		//Lấy danh sách menu hệ thống theo dạng cây
		$mSystemMenu = new Models_SystemMenu();
		$arrMenu = $mSystemMenu->db->select('id,title,languageid')->where('status', 1)
								->where('parentid', $parentId)->getFieldsArray();
		$htmlMenu = '';
		if (!empty($arrMenu))
		{
			if ($parentId == '0')
				$htmlMenu = '<ul id="menuTreeview" class="menuTreeview">';
			else
				$htmlMenu = '<ul class="menuTreeview">';
            if(!empty($arrValue))
            {
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
		}
		return $htmlMenu;
	}
	
	/**
	 * Hiển thị thông tin người dùng đăng nhập
	 */
	function showUserInfoAction(){
		if (isset($_SESSION['pt_control_panel']['system_username']))
		{
			$this->tpl->assign("username", strtoupper($_SESSION['pt_control_panel']["system_username"]));
			$this->tpl->assign('changePassLink', $this->url->action('changePass'));
			$this->tpl->assign('logoutLink', $this->url->action('logout', 'Index', 'ControlPanel'));
			$this->tpl->assign('profileLink', $this->url->action('profile'));
		}
		$this->unloadLayout();
		return $this->view();
	}

	/**
	 * lấy danh sách người dùng - select
	 */
	function getCmbUser($name = 'userid', $selectValue = '', $showfirst = false, $style="width: 245px;")
	{
		$model = new Models_User();
		$html = '<select name="'.$name.'" id="'.$name.'" class="field" style="'.$style.'">';
		$arr = $model->db->select('id,fullname')->orderby('fullname')->getFieldsArray();
		if ($arr)
		{
			if ($showfirst)
				$html .= '<option value="">.......</option>';
			foreach ($arr as $row)
			{
				if ($row['id'] == $selectValue)
					$html .= '<option selected="selected" value="'.$row['id'].'">'.$row['fullname'].'</option>';
				else
					$html .= '<option value="'.$row['id'].'">'.$row['fullname'].'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
	
	/**
	 * Trang profile
	 */
	function profileAction()
	{
		# -- definition
		$msg = '';
		$u = $this->userId;
		$user = Models_User::getById($u);
		
		//Hiển thị upload file nếu chưa có avatar, ngược lại: hiển thị xóa.
		if (!file_exists($user['avatar']))
		{
			$fileHtml = '<input id="ajaxFileUpload" name="ajaxFileUpload" type="file"/>
				<a href="#UploadFile" class="button no-underline" onclick="UploadFileAjax()">Upload</a>';
		}
		else
		{
			$fileHtml = '<img src="/'. $user['avatar'] .'" width="50"/>  <a class="button" href="#" onclick="ajaxFileDelete()">Xóa</a>';
		}
		$this->tpl->assign('fileHtml', $fileHtml);
		$this->tpl->assign('user', $user);

		$this->tpl->assign('msg', $msg);
		# parse and out
		return $this->view();
	}
	
	function profilePost(Models_User $model)
	{
		if (!$model->Update())
		{
			die('Lỗi: ' . $this->db->ErrorMsg());
		}
		else
		{
			$staffid = $this->db->GetFieldValue("select staffid from users where id='$this->user'");
			//update staff info
			$sql = "update staffs set email='$email',fullname='$fullname',birthday='$birthday',bank_account='$bank_account',
			identity_card='$identity_card', phone='$phone', ipphone='$ipphone' where id='$staffid'";
			if (!$this->db->Execute($sql))
			{
				die('Lỗi: ' . $this->db->ErrorMsg());
			}
		}
		$this->url->redirectAction(array('profile', 'Users', 'users'));
	}
	
	function uploadFilePost()
	{
		$error = "";
		$msg = "";
		$file = $this->params['ajaxFileUpload'];
		if(!empty($file['error']))
		{
			switch($file['error'])
			{
		
				case '1':
					$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
					break;
				case '2':
					$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
				case '3':
					$error = 'The uploaded file was only partially uploaded';
					break;
				case '4':
					$error = 'Chưa chọn file đính kèm.';
					break;
		
				case '6':
					$error = 'Missing a temporary folder';
					break;
				case '7':
					$error = 'Failed to write file to disk';
					break;
				case '8':
					$error = 'File upload stopped by extension';
					break;
				case '999':
				default:
					$error = 'No error code avaiable';
			}
		}
		elseif(empty($file['tmp_name']) || $file['tmp_name'] == 'none')
		{
			$error = 'No file was uploaded..';
		}
		else
		{
			$name = $file["name"];
			$name = explode('.', $name);
			$total = count($name);
			$extend = $name[($total - 1)];
			$newname = @$_SESSION['system_username'];
		
			$loc = "UserFiles/users/avatar/".$newname . '.' . $extend;
			$this->db->makeDir('UserFiles/users/avatar');
			move_uploaded_file($file["tmp_name"], Url::getAppDir() ."/".$loc);
			@unlink($file["tmp_name"]);
			$fileDir = $loc;
			$msg = $loc;
		}
		return json_encode(array('error' => $error, 'msg' => $msg, 'fileDir' => $fileDir, 'filename' => $newname . ".$extend"));
	}
	
	function deleteFileAjax()
	{
		//Xóa file đính kèm
		if (!unlink(Url::getAppDir()."/".$this->params['filename']))
			return json_encode(array('success' => false, 'msg' => 'Lỗi xóa file'));
		else
			return json_encode(array('success' => true));
	}
	/*Kích hoạt tài khoản người dùng  */
	public function activeUserAction()
	{
		if(!empty($this->params['act']))
		{
			$this->unloadLayout();
			$model_user = new Models_User();
			$cond = "md5(concat(id,username)) = '".$this->params['act']."'";
			$user = $model_user->db->select('id,username')->where($cond)->getRow();
			if(count($user) > 0)
			{
				$link = $this->url->action('login','Component','index',array('email'=>$user['username']));
				if($this->params['user'] == 'empee')
					$link = $this->url->action('login','Component','index',array('email'=>$user['username']));
				if($this->checkStatus($user['id']))
				{
					echo "<script> window.location.href='".$link."'; </script>";
				}
				else
				{
					$model_user->id = $user['id'];
					$model_user->status = 1;
					$model_user->Update();
					$this->tpl->assign('link',$link);
					return $this->view();
				}
			}
		}
	}
	function checkStatus($id)
	{
		$model_user = new Models_User();
		$cond = "id= $id AND status = 1";
		$num = $model_user->Count($cond);
		if($num >0)
		{
			return true;
		}
		else
			return false;
	}

}