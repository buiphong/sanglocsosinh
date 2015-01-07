<?php
class MemberCPController extends Controller
{
	private $arrGender = array(0=>'Chưa xác định', 1=>'Nam',2=>'Nữ');
	private $arrStatus = array(0=>'Chưa kích hoạt', 1=>'Kích hoạt');
    private  $arrVip = array(0=>'Bình thường', 1=>'Vip');
    private $arrPublic = array(0=>'private', 1=>'Public');
	function __init(){
		//$this->checkPermission();
		$this->loadTemplate('Metronic');
        $this->loadModule(array('ConfigCP'));
	}
	
	function indexAction(){
		$this->tpl->assign('listLink',$this->url->action('index'));
		$this->tpl->assign('createLink', $this->url->action('create'));
		$this->tpl->assign('deleteLink', $this->url->action('delete'));
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'controlpanel'));
		$this->tpl->assign('menuLink', $this->url->action('list', 'MenuType', 'menu'));
		return $this->view();
	}
	public function checkUserNameAjax()
    {
		if(!empty($this->params['us'])){
			$model = new Models_Member();
			$model->db->where('username', @$this->params['us']);
			if(!empty($this->params['id']))
				$model->db->where('id <>', @$this->params['id']);
			
			$datas = $model->db->select("id")->getField();
			
			if(!empty($datas))
				return json_encode(false);
		}
		return json_encode(true);
	}
	public function checkEmailAjax()
    {
		if(!empty($this->params['email'])){
			$model = new Models_Member();
			$model->db->where('email', @$this->params['email']);
			if(!empty($this->params['id']))
				$model->db->where('id <>', @$this->params['id']);
			
			$datas = $model->db->select("id")->getField();

			if(!empty($datas))
				return json_encode(false);
		}
		return json_encode(true);
	}
	public function dataTableAjax()
    {
        $model = new Models_Member();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        if(isset($this->params['sSearch'])){
            $model->db->like('fullname', $this->params['sSearch']);
            //$model->db->like('username', $this->params['sSearch']);
            //$model->db->like('email', $this->params['sSearch']);
		}
        /*Ordering*/
        if ( isset( $_GET['iSortCol_0'] ) ){
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->limit($pageSize, $offset)->getFieldsArray();
        if(!empty($datas)){
            foreach($datas as $key => $val){
                $datas[$key]['editLink'] = $this->url->action("edit", array("id"=>$val["id"]));
                $datas[$key]['detailLink'] = $this->url->action("viewDetail", array("id"=>$val["id"]));
                $datas[$key]['changePassLink'] = $this->url->action("changePass", array("id"=>$val["id"]));
                $datas[$key]['created_date'] = date("d-m-Y", strtotime($val['created_date']));
                $datas[$key]['viewBookingLink'] = '<a  href="javascript:" class="btn mini purple frm-edit-btn-ajax" ';
                $datas[$key]['viewBookingLink'] .= 'style="display: none;">';
                $datas[$key]['viewBookingLink'] .= '<i class="icon-file"></i></a>';
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
    public function dataTableSearchAjax()
    {
        $model = new Models_Member();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

        if(empty($this->params['sSearch']))
            $model->db->like('title', @$this->params['txt_search']);
        if(isset($this->params['sSearch']))
            $model->db->like('title', $this->params['sSearch']);
        if ( isset( $_GET['iSortCol_0'] ) ){
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
        }
        $totalRow = $model->db->count()?$model->db->count():0;
        $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
        $datas = $model->db->limit($pageSize, $offset)->getFieldsArray();
        if(!empty($datas))
        {
            foreach($datas as $key => $val)
            {
                $datas[$key]['image'] = '';
                $datas[$key]['modify'] = date("d-m-Y", strtotime($val['modified']));
                if(isset($val['image_path']))
                    $datas[$key]['image'] = '<img src="'.$this->url->getThumb($val['image_path'], 80,50) . '"/>';
                $href = $this->url->action('index','CategoryCP','ECProductCP',array('parent_id'=>$val['id']));
                $datas[$key]['title'] = "<a href='$href'>".$val['title']."</a>";
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
	public function createAjax(){
		$this->setView('edit');
		$this->tpl->assign('catName', 'Thêm mới');
		
        $modelType = new Models_MemberType();
        $arrType = $modelType->db->select('id,name')->getFieldsArray();
        if(!empty($arrType))
        {
            $this->tpl->assign('slMemberType',$this->html->genSelect('type_id',$arrType,'','id','name'));
        }
        $this->tpl->assign('rdPublic',$this->html->genRadio('public',$this->arrPublic,1));
        $this->tpl->assign('ckVip',$this->html->genCheckbox('is_vip',1));
		$this->tpl->assign('rdGender', $this->html->genRadio('gender',$this->arrGender,''));
		
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('form_action',$this->url->action('createPost'));
        $this->unloadLayout();
		return $this->view();
	}
	
	public function createPostAjax(Models_Member $model){
        if(!empty($this->params['new_password']))
            $model->password = md5($this->params['new_password']);
        else
            $model->password = md5(String::randomString(10));
		$model->birth_date = PTDateTime::formatDate($model->birth_date);
		$model->created_date = date('Y:m:d H:i');
		
		if($model->Insert())
            return json_encode(array('success' => true, 'msg' => "Thêm mới thành công",'dataTable'=>'tableMember'));
        else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	public function editAjax(){
		$model = new Models_Member($this->params['id']);
		if ($model->status)
			$this->tpl->assign('status_checked', 'checked');
		
		$modelType = new Models_MemberType();
        if(!empty($model->birth_date))
		    $model->birth_date = date("d-m-Y", strtotime($model->birth_date));
		$this->tpl->assign('action','Sửa thông tin thành viên');
		$arrType = $modelType->db->select('id,name')->getFieldsArray();
		if(!empty($arrType))
		{
			$this->tpl->assign('slMemberType',$this->html->genSelect('type_id',$arrType,$model->type_id,'id','name'));
		}
		$this->tpl->assign('rdPublic',$this->html->genRadio('public',$this->arrPublic,$model->public));
		$this->tpl->assign('ckVip',$this->html->genCheckbox('is_vip',1,$model->is_vip));
		if($model->type_id == 1)
			$this->tpl->parse('main.customer');
		$this->tpl->assign('rdGender', $this->html->genRadio('gender',$this->arrGender,$model->gender));
			
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('categoryLink', $this->url->action('index'));
        $this->tpl->assign('form_action',$this->url->action('editPost'));
        $this->unloadLayout();
		return $this->view($model);
	}
	public function editPostAjax(Models_Member $model){
        if(empty($this->params['is_vip']))
            $model->is_vip = 0;
        if(!empty($this->params['new_password']))
            $model->password = md5($this->params['new_password']);
        $model->birth_date = PTDateTime::formatDate($model->birth_date);

		if($model->Update())
            return json_encode(array('success' => true, 'msg' => "Cập nhật thông tin thành công",'dataTable'=>'tableMember'));
        else
            return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	function deleteAjax(){
		$model = new Models_Member();
        $ids = $this->params['id'];
        if (strpos($ids, ',') !== false)
        {
            $ids = explode(',', $ids);
            foreach ($ids as $id)
                if ($id != '')
                    if(!$model->Delete("id=$id"))
                        return json_encode(array('success' => false, 'msg' => $model->db->error));

        }
        elseif ($ids != '')
        {
            if(!$model->Delete("id=$ids"))
                return json_encode(array('success' => false, 'msg' => $model->db->error));
        }
        return json_encode(array('success' => true,'dataTable'=>'tableMember'));
	}

    function editProfilePost()
    {
        if(!empty($this->params['mem_id']) && !empty($this->params['type_id']))
        {
            $modelP = new Models_MemberProfile();
            $modelField = new Models_MemTypeField();
            $arrField = $modelField->db->select('id')->where('memtype_id',$this->params['type_id'])->getFieldArray();
            if(!empty($arrField))
            {
                foreach($arrField as $val)
                {
                    if(!isset($this->params['profileVal'][$val]))
                        $this->params['profileVal'][$val] = "";
                }
            }
            $profiles = $this->params['profileVal'];
            foreach($profiles as $key => $vl)
            {
                if(is_array($vl))
                    $vl = ','.implode(',',$vl).',';
                //if(!empty($vl))
                {
                    $memId = $this->params['mem_id'];
                    $prof = $modelP->db->select('id')->where('field_id',$key)->where('member_id',$memId)->getField();
                    if(empty($prof))
                    {
                        //insert
                        if(!empty($vl))
                        {
                            $data = array('value'=>$vl,'edited_date'=>date('Y:m:d H:m'),'created_date'=>date('Y:m:d H:m'),
                                'member_id'=>$memId,'field_id'=>$key);
                            if($modelP->Insert($data))
                                $this->url->redirectAction('index');
                            else
                                die("Can not update data".$modelP->error);
                        }
                    }
                    else
                    {
                        if($modelP->db->where('field_id',$key)->where('member_id',$memId)->update(array('value'=>$vl,'edited_date'=>date('Y:m:d H:m'))))
                            $this->url->redirectAction('index');
                        else
                            die("Can not update data".$modelP->error);
                    }
                }
            }
        }
    }

	function delete_Ajax()
	{
		$model = new Models_Member();
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
	function changePassAjax()
	{
        $this->unloadLayout();
        $this->tpl->assign('form_action',$this->url->action('changePassPost'));
        $this->tpl->assign('id', @$this->params["id"]);
		return $this->view();
	}

	function changePassPostAjax()
	{
		$x_password1 = @$this->params["x_password1"];
		$x_password2 = @$this->params["x_password2"];
		$msg = '';
		$status = 'error';
		if ($x_password1<>$x_password2){
			$msg="Xác nhận mật khẩu mới chưa chính xác!";
		} else {
			if(!empty($this->params["id"])){
				
				$model = new Models_Member();
				$model->db->where('id', $this->params['id']);
				$array = array("password" => md5($x_password1));
				if($model->Update($array)){
					return json_encode(array('success' => true, 'msg' => "Cập nhật thành công"));
				}
				else
					return json_encode(array('success' => false, 'msg' => $model->db->error));
			}
			else
				$msg = "Dữ liệu chưa chính xác!";
		}
        return json_encode(array('success' => true, 'msg' => $msg));
	}
	function sendEmail($sentEmail,$username, $password,$activeLink=''){
		$email = new PTMail();
		$content = '<strong>Thông tin tài khoản thành viên Trung tâm Sàng lọc sơ sinh</strong>
                <br>Tài khoản của bạn đã được tạo thành công. Hãy click vào link <a href="'.$activeLink.'">
                đăng nhập hệ thống</a> để hoàn tất quá trình đăng ký.
                <p>Tên đăng nhập của bạn là:'. $username."</p>
                <p>Mật khẩu đăng nhập là :".$password."</p>";
		return $email->send($sentEmail, 'Tài khoản thành viên Trung tâm Sàng lọc sơ sinh', $content);
	}
	/*Kích hoạt tài khoản người dùng  */
	public function activeMemberAction()
	{
		if(!empty($this->params['act']))
		{
			$this->unloadLayout();
			$model = new Models_Member();
			$cond = "md5(concat(id,username)) = '".$this->params['act']."'";
			$user = $model->db->select('id,username')->where($cond)->getFields();
			if(count($user) > 0)
			{
				$link = $this->url->action('login','MemberComponent','Members',array('username'=>$user['username']));
				if($this->checkStatus($user['id']))
				{
					echo "<script> window.location.href='".$link."'; </script>";
				}
				else
				{
					$model->id = $user['id'];
					$model->status = 1;
					$model->Update();
					$this->tpl->assign('link',$link);
					return $this->view();
				}
			}
		}
	}
	function checkStatus($id)
	{
		$model = new Models_Member();
		$cond = "id= $id AND status = 1";
		$num = $model->Count($cond);
		if($num >0)
		{
			return true;
		}
		else
			return false;
	}
    function changeTypeAjax()
    {
        if(!empty($this->params['id']))
        {
            $id = $this->params['id'];
            $model = new Models_Member();
            if($model->db->where("id = $id")->update(array('type_id'=>$this->params['type_id'])))
                return json_encode(array('success'=>true));
            else
                return json_encode(array('success'=>false));
        }
    }
    function changeVipAjax()
    {
        if(!empty($this->params['id']))
        {
            $id = $this->params['id'];
            $model = new Models_Member();
            if($model->db->where("id = $id")->update(array('is_vip'=>$this->params['is_vip'])))
                return json_encode(array('success'=>true));
            else
                return json_encode(array('success'=>false));
        }
    }
    public function viewDetailAjax()
    {
        $this->unloadLayout();
		$model = new Models_Member();
        if (!empty($this->params['id']))
        {
            $id = $this->params['id'];
            $model = new Models_Member($id);
            $modelType = new Models_MemberType();
            $this->tpl->assign('memberType',$modelType->db->select('name')->where('id',$model->type_id)->getField());
            foreach($this->arrGender as $key => $vl)
            {
                if($key == $model->gender)
                    $model->gender = $vl;
            }
            foreach($this->arrStatus as $key => $vl)
            {
                if($key == $model->status)
                    $model->status = $vl;
            }
            foreach($this->arrVip as $key => $vl)
            {
                if($key == $model->is_vip)
                    $model->is_vip = $vl;
            }
            $model->birth_date = PTDateTime::userDate($model->birth_date);
        }
		return $this->view($model);
    }

    public function viewBookingMemberAjax()
    {
        $this->unloadLayout();
        if(!empty($this->params['id']))
            $this->tpl->assign("member_id", $this->params['id']);
        return $this->view();
    }

    public function listBookingAjax()
    {
        if(!empty($this->params['member_id']))
        {
            $arrTypeBooking = Config::getConfig("BookingCP:type_booking");
            $model = new Models_Booking();
            $listId = $model->getListId(@$this->params["member_id"]);
            if(empty($listId))
                $listId = 0;
            $pageSize = @$this->params['iDisplayLength'];
            $offset = @$this->params['iDisplayStart'];

            if(isset($this->params['sSearch'])){
                $model->db->like('from', $this->params['sSearch']);
            }
            /*Ordering*/
            if (isset( $_GET['iSortCol_0'] ) ){
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_0']) ] == "true" )
                    $model->db->orderby($_GET['mDataProp_'.$_GET['iSortCol_0']] ,$_GET['sSortDir_0']==='asc' ? 'asc' : 'desc');
            }
            $totalRow = $model->db->where_in('id', $listId)->count()?$model->db->where_in('id', $listId)->count():0;
            $data = array('sEcho' => @$this->params['sEcho'], 'iTotalRecords' => $totalRow);
            $datas = $model->db->where_in('id', $listId)->limit($pageSize, $offset)->getFieldsArray();
            if(!empty($datas)){
                foreach($datas as $key => $val){
                    $ticket_info = unserialize($val["ticket_infomation"]);
                    $datas[$key]['fullname'] = $ticket_info["fullname_theticket"];
                    $datas[$key]['type_booking'] = $arrTypeBooking[$val['type_booking']];
                    $datas[$key]['depart_date'] = date("d-m-Y", strtotime($val['depart_date']));
                    $datas[$key]['return_date'] = date("d-m-Y", strtotime($val['return_date']));
                    $datas[$key]['viewPassengerLink'] = $this->url->action("viewPassenger", array("id" => $val["id"]));
                }
            }
            $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
            $data['aaData'] = $datas;
            return json_encode($data);
        }
    }

    public function viewPassengerAjax()
    {
        $this->unloadLayout();
        if(!empty($this->params['id']))
            $this->tpl->assign("booking_id", $this->params['id']);
        return $this->view();
    }

    public function viewBaggageAjax()
    {
        $this->unloadLayout();
        if(!empty($this->params['passenger_id']))
            $this->tpl->assign("passenger_id", $this->params['passenger_id']);
        return $this->view();
    }
}






