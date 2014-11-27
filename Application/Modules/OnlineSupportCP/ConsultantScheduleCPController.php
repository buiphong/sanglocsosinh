<?php
class ConsultantScheduleCPController extends Controller
{
    public $arrStatus = array('Chưa duyệt','Đã duyệt','Toàn bộ');
    public function __init()
    {
        $this->checkPermission();
        $this->loadTemplate('flatadmin');
    }

    public function indexAction(){
        $pageSize = 20;
        $page = @$this->params['page'];
        if (empty($page))
            $page = 1;
        $offset = ($page - 1) * $pageSize;

        if(!empty($this->params['keySearch']))
        {
            $key = $this->params['keySearch'];
            $this->tpl->assign('keySearch', $key);
        }

        if (!isset($this->params['status']))
            $status = '2';
        else
            $status = $this->params['status'];




        $offset = ($page - 1) * $pageSize;
        $model = new Models_ConsultantSchedule();

        if ($status != '2')
            $model->db->where('status', $status);
        if (!empty($this->params['keySearch'])){
            $model->db->orlike('fullname', $this->params['keySearch']);
            $model->db->orlike('phone', $this->params['keySearch']);
        }

        $totalRows = $model->db->count();
        $supports = $model->db->orderby('create_time', 'desc')->limit($pageSize, $offset)->getAll();

        if(!empty($supports)){
            foreach ($supports as $support){
                $support['statusName'] = $this->arrStatus[$support['status']];

                $arrConsultants = unserialize( $support['time_consultant']);

                $tempConsultant = "";
                if(is_array($arrConsultants))
                    foreach($arrConsultants as $arrConsultant){
                        if(isset($arrConsultant['start']) && $arrConsultant['start'] != "" && isset($arrConsultant['end']) && $arrConsultant['end'] != "")
                            $tempConsultant .= date('d-m-Y H:i', strtotime($arrConsultant['start'])) . " đến " . date('d-m-Y H:i', strtotime($arrConsultant['end'])) . "<br/>";
                    }
                $support['time'] = substr($tempConsultant, 3);

                $arrPhones = unserialize( $support['phone']);
                $tempPhone = "";
                if(is_array($arrPhones))
                    foreach($arrPhones as $arrPhone)
                        $tempPhone .= " - " . $arrPhone;
                $support['phones'] = substr($tempPhone, 3);
                $this->tpl->insert_loop('main.support', 'support', $support);
            }
        }

        $this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
        if ($totalRows > 0)
            $this->tpl->parse('main.button');

        $commentStatus = $this->arrStatus;
        foreach ($commentStatus as $key => $s)
        {
            $st = array();
            if ($key == $status)
                $st['checked'] = 'checked';
            $st['title'] = $s;

            $st['key'] = $key;
            $this->tpl->insert_loop('main.status', 'status', $st);
        }


        $this->tpl->assign('deleteLink', $this->url->action('delete'));
        $this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
        $this->tpl->assign('listLink', $this->url->action('index'));
        $this->tpl->assign('changeStatus', $this->url->action('changeStatus'));
        $this->tpl->assign('urlViewDetailAjax', $this->url->action('viewDetail'));
        return $this->view();
    }


    public function viewDetailAjax(){
        $this->unloadLayout();
        $arrFlgName = array("Chưa sử dụng", "Đã sử dụng");
        $arrTypeName = array("Chưa xác định", "Qua email", "Qua điện thoại", "Trực tiếp");
        $model = new Models_ConsultantSchedule();
        if (!empty($this->params['id'])){
            $consultantSchedule = $model->db->select('id,fullname,address,phone,email,consultant_type,time_consultant,status,member_id,mem_num,desc,bionet_flg,create_time')->where('id', $this->params['id'])->getFields();
            if ($consultantSchedule){
                $consultantSchedule['statusName'] = $this->arrStatus[$consultantSchedule['status']];
                $consultantSchedule['bionetFlgName'] = $arrFlgName[$consultantSchedule['bionet_flg']];
                $consultantSchedule['consultantTypeName'] = $arrTypeName[$consultantSchedule['consultant_type']];


                $arrAaddress = unserialize( $consultantSchedule['address']);

                $tempAddress = "";
                if(is_array($arrAaddress))
                    foreach($arrAaddress as $arrAaddress){
                        if($arrAaddress != "")
                            $tempAddress .= " vs " . $arrAaddress;
                    }
                $consultantSchedule['address'] = substr($tempAddress, 4);

                $arrConsultants = unserialize( $consultantSchedule['time_consultant']);

                $tempConsultant = "";
                if(is_array($arrConsultants))
                    foreach($arrConsultants as $arrConsultant){
                        if(isset($arrConsultant['start']) && $arrConsultant['start'] != "" && isset($arrConsultant['end']) && $arrConsultant['end'] != "")
                            $tempConsultant .= date('d-m-Y H:i', strtotime($arrConsultant['start'])) . " đến " . date('d-m-Y H:i', strtotime($arrConsultant['end'])) . "<br/>";
                    }
                $consultantSchedule['time'] = substr($tempConsultant, 3);

                $arrPhones = unserialize( $consultantSchedule['phone']);
                $tempPhone = "";
                if(is_array($arrPhones))
                    foreach($arrPhones as $arrPhone)
                        $tempPhone .= " - " . $arrPhone;
                $consultantSchedule['phones'] = substr($tempPhone, 3);
                $consultantSchedule['create_time'] = date('d-m-Y H:i', strtotime($consultantSchedule['create_time']));

                $this->tpl->assign('consultantSchedule', $consultantSchedule);
                return json_encode(array('success' => true, 'html' => $this->view()));
            }
            else
                return json_encode(array('success' => false, 'msg' => 'Không tìm thấy'));
        }
        else
            return json_encode(array('success' => false, 'msg' => 'Dữ liệu không chính xác'));
    }
    function deleteAjax(){
        $model = new Models_ConsultantSchedule();
        $ids = $this->params['listid'];
        if (strpos($ids, ',') !== false){
            $ids = explode(',', $ids);
            foreach ($ids as $id)
                if ($id != '')
                    if(!$model->Delete("id=$id")){
                        return json_encode(array('success' => false, 'msg' => $model->error));
                        break;
                    }
        }
        elseif ($ids != ''){
            if(!$model->Delete("id=$ids"))
                return json_encode(array('success' => false, 'msg' => $$model->error));
        }
        return json_encode(array('success' => true, 'link' => $this->url->action('index')));
    }
    public function changeStatusAjax()
    {
        if (!empty($this->params['id']))
        {
            //Update status
            $id = $this->params['id'];
            if ($this->params['oldStatus'] == '0')
            {
                $status = 1;
                $statusName = 'Đã duyệt';
            }
            else
            {
                $status = 0;
                $statusName = 'Chưa duyệt';
            }
            $model = new Models_ConsultantSchedule();
            if($model->db->where('id', $id)->update(array('status'=>$status)))
                return json_encode(array('success' => true, 'status' => $status, 'statusName' => $statusName));
            else
                return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
        }
        return json_encode(array('success' => false, 'msg' => 'Thông tin nhập vào chưa chính xác'));
    }
}