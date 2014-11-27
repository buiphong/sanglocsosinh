<?php
class IndexController extends Controller
{
	function __init()
	{
        $this->loadTemplate('Metronic');
        $this->loadModule('UsersCP');
	}
	
	public function indexAction()
	{
        if(empty($_SESSION['pt_control_panel']["system_username"]))
            $this->url->redirectAction('login');
        $this->loadLayout('index');

        if(Helper::moduleExist('NewsCP'))
        {
            //Đếm tổng số tin bài
            $this->loadModule('NewsCP');
            $this->tpl->assign('totalNews', Models_News::countByStatus());
            $this->tpl->parse('main.news');
        }

        if(Helper::moduleExist('MembersCP'))
        {
            //Đếm tổng số thành viên
            $this->loadModule('MembersCP');
            $this->tpl->assign('totalMember', Models_Member::countMember());
            $this->tpl->parse('main.member');
        }

        if(Helper::moduleExist('ContactsCP'))
        {
            //Thông báo liên hệ mới
            $this->loadModule('ContactsCP');
            $this->tpl->assign('totalContact', Models_Contact::countByStatus(0));
            $this->tpl->parse('main.contact');
        }

        if(Helper::moduleExist('FaqCP'))
        {
            //Hiển thị thông báo nếu có câu FAQ chưa được trả lời
            $this->loadModule('FaqCP');
            $unAnswer = Models_Faqs::checkUnAnswer();
            if($unAnswer !== false)
            {
                $this->tpl->assign('totalFaq', $unAnswer);
                $this->tpl->parse('main.faq');
            }
        }

		return $this->view();
	}
	
	public function indexAjax()
	{
		return json_encode(array('success' => true, 'username: ' . $this->params['username']));
	}
	
	public function loginAction()
	{
		if(!empty($_SESSION['pt_control_panel']["system_username"]))
			$this->url->redirectAction('index');
		$this->loadLayout('login');
		$this->tpl->assign('frmAction', $this->url->action('login'));
		$this->tpl->assign('captcha', $this->html->showCaptcha());
		$this->tpl->assign('msg', @$this->params['msg']);
		$this->tpl->assign('loginUrl', $this->url->action('login', $this->params));
		return $this->view();
	}
	
	function loginAjax(Models_Login $model)
	{
        $user = $model->checkLogin();
        if(!$user){
            return json_encode(array('success' => false, 'msg' => 'Tên tài khoản hoặc mật khẩu chưa chính xác!', 'newCaptcha' => $this->html->showCaptcha()));
        }
        else{
            //Lấy nhóm quyền người dùng
            $arrRole = explode(',', $user['roles']);
            $arrLevel = array();
            $modelRole = new Models_Roles();
            foreach ($arrRole as $role)
            {
                //Lưu userlevel thành mảng
                $row = $modelRole->db->getFields("id='$role'");
                $arrLevel[] = $row['rolelevel'];
            }

            $_SESSION['pt_control_panel']["system_userid"] = $user["id"];
            $_SESSION['pt_control_panel']["system_username"] = $user["username"];
            $_SESSION['pt_control_panel']["password"] = $user["password"];
            $_SESSION['pt_control_panel']['system_userlevel'] = $user['permission'];
            //Update session langcode
            if(MULTI_LANGUAGE)
            {
                $this->loadModule('LanguagesCP');
                $_SESSION["sys_langcode"] = Models_Language::getDefaultLangcode();
            }

            # update lastlogin and lastsession
            $arrData = array('lastlogin' => date('Y-m-d H:i:s'), 'lastsession' => session_id());
            $this->model->Update($arrData);

            # return true for success
            return json_encode(array('success' => true,
                'url' => !isset($this->params['url'])?$this->url->action('index'):base64_decode($this->params['url'])));
        }
	}
		
	function logoutAction()
	{
		@session_unset();
		@session_destroy();
	    return $this->url->redirectAction('login');
	}
	
	public function errorAction()
	{
		$this->loadLayout('error');
		$this->tpl->assign('code', $this->params['code']);
		$this->tpl->assign('desc', $this->params['desc']);
		$this->tpl->assign('url', $this->params['url']);
		return $this->view();
	}

    public function deleteDbCacheAjax()
    {
        PTDirectory::emptyDir(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR, false);
        return json_encode(array(
            'success' => true,
            'newSize' => number_format(PTDirectory::getDirSize(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR) / (1024*1024), 3, ',', '.') . ' Mb'));
    }

    public function deleteHtmlCacheAjax()
    {
        PTDirectory::emptyDir(ROOT_PATH . DIRECTORY_SEPARATOR . CACHE_FRONTEND_DIR, false);
        return json_encode(array(
            'success' => true,
            'newSize' => number_format(PTDirectory::getDirSize(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR) / (1024*1024), 3, ',', '.') . ' Mb'));
    }

    public function deleteAssetCacheAjax()
    {
        PTDirectory::emptyDir(ROOT_PATH . DIRECTORY_SEPARATOR . COMBINE_ASSETS_DIR, false);
        return json_encode(array(
            'success' => true,
            'newSize' => number_format(PTDirectory::getDirSize(ROOT_PATH . DIRECTORY_SEPARATOR . ADODB_CACHE_DIR) / (1024*1024), 3, ',', '.') . ' Mb'));
    }
}