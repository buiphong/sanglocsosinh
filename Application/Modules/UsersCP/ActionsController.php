<?php
class ActionsController extends Controller
{
	function __init()
	{
		$this->checkPermission();
		$this->loadTemplate('admin');
		$this->loadLayout('index');
        $this->loadModule('ActionsCP');
	}
	
	/*
	 Show action group by actiongroups
	*/
	function showCheckboxes($name, $statusValues, $disabled=0, $className="treeview-red", $id='', $ten=''){
		$model = new Models_Action();
        $modelGroup = new Models_ActionGroup();
		$rs1 = $modelGroup->db->select('id, name')->getFieldsArray();
		if (!$rs1) return "A";
		$treeId = "actions_tree";
		$str = "<ul id='".$treeId."' class='$className'>";
		foreach ($rs1 as $action)
		{
			$rs2 = $model->db->select('name,id')->where('groupid', $action['id'])->getFieldsArray();
            $str.="<li><"."input class='chksysactions' type='checkbox' name='$name"."[]' value=''/>".$action["name"]."\n";
			//Gen multicheckbox childrent
			$str.="<ul>".$this->html->genMultiCheckboxesFromRs($name, $rs2, $statusValues,$disabled, $id, $ten)."</ul>\n";
			$str.="</li>\n";
		}
		$str.= "</ul>\n";
		$str.= $this->html->treeJS($treeId);
		return $str;
	}//end
}