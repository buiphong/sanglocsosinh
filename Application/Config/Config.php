<?php
/**
 * Thông tin cấu hình ứng dụng
 */
$session_user = "system_username";
$session_admin = "system_userlevel";
$session_admin_value = "-1";
$has_security = "1";
$login_page = array('login', 'Index', 'ControlPanel');
$error_router = array(
    '404' => array(
        'alias' => 'Portal',
        'module' => 'Index',
        'controller' => 'Error',
        'action' => 'error404'
    )
);
//$currency = '$';
//100.000vnđ thanh toán được tính thành 1 điểm
$convertPoint = 100000;