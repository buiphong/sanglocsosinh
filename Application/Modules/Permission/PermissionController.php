<?php
class PermissionController extends Controller
{
	function checkPermissionAction()
	{
        $this->loadModule(array('ActionsCP','UsersCP'));
        $router = $this->params['router'];
        $modelAction = new Models_Action();
        //check action
        $action = $modelAction->db->select('id')->where('module', $router->module)
                        ->where('controller', $router->controller)->where('action', $router->action)
                        ->getField();
        if (!empty($action)) {
            //Check user permission
            $modelUser = new Models_User();
            $user = $modelUser->db->where('username', $_SESSION['pt_control_panel']['system_username'])
                        ->where('password', $_SESSION['pt_control_panel']['password'])->getFields();

            $modelRoles = new Models_Roles();
            $roles = $modelRoles->db->select('actions')->where('id', $user['roles'])->getFields();
            $list = $roles['actions'];
            if($user['moreactions'])
                $list .= ','.$user['moreactions'];
            $list = explode(',', $list);
            if (!in_array($action, $list)) {
                return true;
            }
        }
        return true;
    }
}