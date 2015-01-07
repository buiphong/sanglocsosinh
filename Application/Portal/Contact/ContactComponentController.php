<?php
class ContactComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule(array('ContactsCP', 'ConfigCP', 'BionetContactCP'));
    }

    public function contactPageAction()
    {
        $configs = Models_ConfigValue::getConfig('mail_receive,website_hotline,website_phone,website_address');
        $this->tpl->assign('form_action', $this->url->action('contactPage'));
        //Lấy danh sách điểm thu mẫu
        $list = Models_BionetContact::getList();
        if($list)
        {
            $i = 1;
            foreach($list as $v)
            {
                if($i % 2 == 0)
                {
                    $this->tpl->parse('main.listContact.clear');
                    $v['class'] = 'tar';
                }
                else
                    $v['class'] = 'tal';
                $this->tpl->insert_loop('main.listContact', 'item', $v);
                $i++;
            }
        }
        $this->tpl->assign("address", $configs['website_address']);
        $this->tpl->assign("phone",  $configs["website_phone"]);
        $this->tpl->assign("hotline", $configs["website_hotline"]);
        //$this->tpl->assign("captcha", $this->html->showCaptcha());

        return $this->view();
    }

    public function contactPageAjax(Models_Contact $model)
    {
        $model->create_date = date('Y-m-d H:i');
        if($this->model->Insert())
        {
            //Send mail
            //Lấy cấu hình email nhận liên hệ
            $configs = Models_ConfigValue::getConfig('conf_mail_receive,website_name,conf_email_name');
            if (!empty($configs['conf_mail_receive']))
            {
                $mail = new PTMail();
                $content = 'Bạn nhận được một tin liên hệ mới trên website: ' .$configs['website_name']. '<br>'.
                            '<strong>Từ:</strong> <i>' . $model->fullname. '</i><br>'.
                            '<strong>Địa chỉ email:</strong> <i>' . $model->email . '</i><br>'.
                            '<strong>Điện thoại:</strong> <i>' . $model->phone. '</i><br>'.
                            '<strong>Với nội dung:</strong> <i>' . $model->content. '</i>';
                if (!$mail->send($configs['conf_mail_receive'], 'Thông tin liên hệ mới', $content, '','', $configs['conf_email_name']))
                    return json_encode(array('success' => false, 'msg' => $mail->getErrorMessage()));
            }
            return json_encode(array('success' => true, 'msg' => 'Bạn đã gửi thông tin liên hệ thành công! Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất! Xin cảm ơn!'));
        }
        else
            return json_encode(array('success' => false, 'msg' => $this->model->error));
    }
}