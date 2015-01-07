<?php
class PTMail{
	private $Host = 'mail.vietclever.com';
	private $SMTPAuth = true;
	private $Port = 25;
	private $from = 'webmaster@vcc.vn';
	private $fromName = 'Vcc.vn';
	private $CharSet = 'UTF-8';
	private $username = 'cv.noreply@vietclever.com';
	private $password = 'cv@123.vc';
	private $Mail;
	
	public function __construct()
	{
        Helper::loadModule('ConfigCP');
		//get email config
		$emailConf = Models_ConfigValue::getConfig('conf_email_from,conf_email_name,conf_email_send_type,conf_email_host,
		conf_email_port,conf_email_auth,conf_email_username,conf_email_pass');
		$this->Mail = new PHPMailer();
		$this->Mail->IsSMTP();
		//$this->Mail->SMTPDebug = 1;
		$this->Mail->CharSet= $this->CharSet;
		$this->Mail->SMTPAuth = $this->SMTPAuth;

        $this->Mail->Host = $this->Host;
        $this->Mail->Port = $this->Port;
        $this->Mail->Username = $this->username;
        $this->Mail->Password = $this->password;

		if ($emailConf) {
			$this->Mail->Host = $emailConf['conf_email_host'];
			$this->Mail->Port = $emailConf['conf_email_port'];
			$this->Mail->Username = $emailConf['conf_email_username'];
			$this->Mail->Password = $emailConf['conf_email_pass'];
            $this->Mail->SMTPAuth = true;
			$this->from = $emailConf['conf_email_from'];
			$this->fromName = $emailConf['conf_email_name'];
		}
	}
	
	public function send($email, $subject, $content, $altBody = 'Mail được gửi bởi Vcc.vn', $from = '', $fromName = '')
	{
		//Kiểm tra cấu hình mail gửi đi trong CSDL
		if (empty($from))
			$from = $this->from;
		if (empty($fromName))
			$fromName = $this->fromName;
		
		$this->Mail->From = $from;
		$this->Mail->FromName = $fromName;
        //Clear address
        $this->Mail->ClearAddresses();
		$this->Mail->AddAddress($email);
		$this->Mail->AddReplyTo($from);
		$this->Mail->WordWrap = 50;
		$this->Mail->IsHTML(true);
		$this->Mail->Subject = $subject;
		$this->Mail->Body = $content;
		$this->Mail->AltBody = $altBody;
		$send = $this->Mail->Send();
		if (!$send){
			return false;
		}
		else
			return true;
	}
	
	public function getErrorMessage()
	{
		$msg = $this->Mail->ErrorInfo;
		return $msg;
	}
}
?>