<?php
class OnlineSupportController Extends Presentation
{
    public function __init()
    {
        $this->loadModule(array("OnlineSupportCP", "ConfigCP"));
    }

    public function boxSupportOnlineAction()
    {
        $hotline = Models_ConfigValue::getConfValue("website_hotline");
        $hotline = explode("-", $hotline);
        foreach($hotline as $v)
        {
            $this->tpl->assign("hotline", $v);
            $this->tpl->parse("main.hotline");
        }
        $email = Models_ConfigValue::getConfValue("website_email");
        $this->tpl->assign("email", $email);
        return $this->view();
    }
}