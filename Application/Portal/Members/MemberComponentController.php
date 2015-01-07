<?php
class MemberComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule("MembersCP");
    }

    public function boxLoginAction()
    {
        $this->tpl->assign("registerAction", $this->url->action("register"));
        if(!empty($_SESSION["member"]))
        {
            $this->tpl->assign("linkLogout", $this->url->action("logout"));
            $fullName = $_SESSION["member"]["fullname"];
            if(empty($fullName))
                $fullName = $_SESSION["member"]["username"];
            $this->tpl->assign("fullname", $fullName);
            $this->tpl->assign("urlProfile", $this->url->action("profile"));
            $this->tpl->parse("main.boxLogout");
        }
        else
        {
            $this->tpl->assign("loginAction", $this->url->action("login"));
            $this->tpl->parse("main.boxLogin");
        }
        return $this->view();
    }

    public function registerAction()
    {
        //$this->tpl->assign("captcha", $this->html->showCaptcha());
        $this->tpl->assign("form_action", $this->url->action('register'));
        $this->loadModule('ListCP');
        //Danh sách tỉnh thành
        $province = Models_ListProvince::getTreeView();
        foreach($province as $k => $v)
        {
            $this->tpl->insert_loop('main.province', 'province', array('id' => $k, 'title' => $v));
        }
        $this->tpl->assign('captcha', Html::getCaptcha());
        $this->viewParam->title = 'Đăng ký thành viên';
        return $this->view();
    }

    public function registerAjax()
    {
        $data = $this->params;
        if(!empty($data))
        {
            //Check for captcha
            if($this->html->validateCaptcha($this->params['captcha_code']))
                if($data["password"] == $data["re-password"])
                {
                    unset($data['captcha_code']);
                    $msg = $this->checkMember($data['username'],$data['email']);
                    if(empty($msg))
                    {
                        $model = new Models_Member();
                        $data['type_id'] = 1;
                        $data['password'] = md5($data['password']);
                        $data['created_date'] = date('Y-m-d H:i:s');
                        $data['status'] = 1;
                        if(!empty($data['birth_date']))
                            $data['birth_date'] = PTDateTime::formatDate($data['birth_date']);
                        foreach($data as $k => $v)
                        {
                            if(empty($v))
                                unset($data[$k]);
                        }
                        unset($data['agree_condition']);
                        unset($data["re-password"]);
                        unset($data["captcha"]);
                        if($model->Insert($data))
                            return json_encode(array('success'=>true, "msg" => "Bạn đã đăng ký thành công!"));
                        else
                            return json_encode(array('success'=>false,'msg'=>$model->error));
                    }
                    else
                        return json_encode(array('success'=>false,'msg'=>$msg));
                }
                else
                    return json_encode(array("success" => false, "msg" => "Mật khẩu và xác nhận mật khẩu phải trùng nhau."));
            else
                return json_encode(array("success" => false, "msg" => "Mã an toàn chưa đúng!"));

        }
        else
            return json_encode(array("success" => false, "msg" => "Dữ liệu chưa chính xác, vui lòng kiểm tra lại dữ liệu bạn đã nhập!"));

    }

    public function loginAjax()
    {
        $data = $this->params;
        if(!empty($data))
        {
            //$model = new Models_Member();
            $member = $this->checkLogin($data['username'], $data['password']);
            if($member)
            {
                if($data["remember"] == 1)
                {
                    setcookie('username',$this->params['username'], time() + 30*24*3600, '/');
                    setcookie('id',$member['id'], time() + 30*24*3600, '/');
                }
                $_SESSION['member'] = array(
                    'id' => $member['id'],
                    'fullname' => $member['fullname'],
                    'email' => $member['email'],
                    'address' => $member['address'],
                    'phone' => $member['phone'],
                    'username' => $member['username']
                );
                return json_encode(array('success'=>true, 'msg' => $data["remember"],
                    'html' => $this->html->renderAction('boxLogin')));
            }
            else
                return json_encode(array('success'=>false,'msg'=>"Tên đăng nhập hoặc mật khẩu chưa chính xác!"));
        }
        else
            return json_encode(array('success'=>false));
    }

    public function logoutAction()
    {
        unset($_SESSION['member']);
        setcookie('username',@$_SESSION["username"], time() - 3600, '/');
        setcookie('id'," ", time() - 3600, '/');
        $this->url->redirectAction("index", "Index", "Index");
    }

    public function profileAction()
    {
        $this->loadModule('ListCP');
        if(!isset($_SESSION['member']))
            return $this->html->renderAction('boxLogin');
        $member = Models_Member::getById($_SESSION['member']['id']);
        if($member['birth_date'])
            $member['birth_date'] = date('d/m/Y', strtotime($member['birth_date']));
        //Danh sách tỉnh thành
        $province = Models_ListProvince::getTreeView();
        foreach($province as $k => $v)
        {
            $selected = '';
            if($member['province_id'] == $k)
                $selected = 'selected';
            $this->tpl->insert_loop('main.province', 'province', array('id' => $k, 'title' => $v, 'selected' => $selected));
        }
        $this->tpl->assign('member', $member);
        return $this->view();
    }

    /**
     * Update profile
     */
    public function updateProfileAjax(Models_Member $model)
    {
        if(!empty($model->birth_date))
            $model->birth_date = PTDateTime::formatDate($model->birth_date);
        if(!$model->Update())
            return json_encode(array('success' => false, 'msg' => 'Xảy ra lỗi khi thực hiện cập nhật thông tin, vui lòng thử lại sau.'));
        return json_encode(array('success' => true, 'msg' => 'Cập nhật thông tin cá nhân thành công.'));
    }

    public function changePassAjax()
    {
        //Kiểm tra password cũ
        if(empty($this->params['password']) || empty($this->params['old_password']) || empty($this->params['re-password']))
            return json_encode(array('success' => false, 'msg' => 'Dữ liệu chưa đúng, vui lòng nhập đầy đủ mật khẩu cũ, mật khẩu mới, xác nhận mật khẩu mới.'));
        else
        {
            $model = new Models_Member($_SESSION['member']['id']);
            if($model->password != md5($this->params['old_password']))
                return json_encode(array('success' => false, 'msg' => 'Mật khẩu cũ không chính xác.'));
            if($model->db->where('id', $_SESSION['member']['id'])->update(array('password' => md5($this->params['password']))))
                return json_encode(array('success' => true, 'msg' => 'Thay đổi mật khẩu thành công!'));
            else
                return json_encode(array('success' => false, 'msg' => 'Đã có lỗi xảy ra khi thực hiện thay đổi mật khẩu, vui lòng thử lại sau'));
        }
    }

    public function checkMember($username='',$email = '')
    {
        $model = new Models_Member();
        $checkUser = $model->db->select('id')->where('username',$username)->getField();
        $checkMail = $model->db->select('id')->where('email',$email)->getField();
        $msg = '';
        if(!empty($checkUser))
            $msg = 'Tên đăng nhập đã được sử dụng, vui lòng chọn tên đăng nhập khác!';
        if(!empty($checkMail))
            $msg = 'Email bị trùng!';
        if(!empty($checkMail) && !empty($checkUser))
            $msg = "Email đã được sử dụng, vui lòng chọn một emal khác!";
        return $msg;
    }

    public static function checkLogin($username, $password)
    {
        $modelMember = new Models_Member();
        if (!empty($username) && !empty($password))
        {
            $password = md5($password);
            $result = $modelMember->db->where('username',$username)->where('password', $password)->getFields();
            if ($result)
                return $result;
        }
        return false;
    }

    /**
     * manager sidebar member
     */
    public function sidebarManagerAction()
    {
        if(isset($_SESSION['member']['id']))
        {
            //Link đăng sản phẩm
            $this->tpl->assign('linkCreateProuct', $this->url->action('postProduct', 'ShopComponent', 'Shop'));
            //Link mở gian hàng
            $this->tpl->assign('linkCreateShop', $this->url->action('memberCreateShop', 'ShopComponent', 'Shop'));
            //Danh sách gian hàng
            $this->tpl->assign('linkListShopMember', $this->url->action('listShopMember', 'ShopComponent', 'Shop'));
            //Danh sách sản phẩm
            $this->tpl->assign('linkListProuct', $this->url->action('listProductMember', 'ShopComponent', 'Shop'));

            $this->tpl->assign('profileLink', $this->url->action('profile'));
            return $this->view();
        }
    }
}