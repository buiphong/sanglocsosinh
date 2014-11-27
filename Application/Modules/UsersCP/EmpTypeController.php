<?php
class EmpTypeController extends Controller{
     public function __init(){
        $this->checkPermission();
        $this->loadTemplate("Metronic");
     }
     public function indexAction(){
        $table = "emp_type";
        $flds="emptype_id,emptype_name,emptype_type";
        $pageSize = 15;
        $page = @$this->params['page'];
		if (empty($page) || $page < 0)
			$page = 1;
		$modelType = new Models_EmpType();
		$offset = ($page - 1) * $pageSize;
        $sql="SELECT $flds FROM $table";
        //$totalRows = $modelType->db->GetFieldValue("SELECT count(emptype_id) as total FROM $table");
        $totalRows = $modelType->Count();
        //print_r($totalRows);
        $EmpType=$modelType->db->getFieldsArray();
        foreach($EmpType as $emptype){         
            $this->tpl->assign('editLink',$this->url->action('edit',array('key'=>$emptype['emptype_id'])));
            $this->tpl->insert_loop('main.emptype','emptype',$emptype);
        }
        $this->tpl->assign('deleteLink',$this->url->action('delete'));
        $this->tpl->assign('createLink',$this->url->action('create'));        
        $this->tpl->assign('frmAction', $this->url->getUrlAction(array('index', 'EmpType', 'users')));
        $this->tpl->assign('PAGE', $this->helper->pagging($page, $pageSize, $totalRows));
        if($totalRows)
        $this->tpl->insert_loop('main.button','button',$emptype);
        return $this->view();
     }
     
     public function createAction(){
        $this->setView('edit');
        $this->tpl->assign('listLink',$this->url->action('index'));
       
        return $this->view();
     }
     public function createPost(Models_EmpType $emp){
       // $table = "emp_type";
//		$flds = "emptype_name,emptype_type";
//		$flds = str_replace(";", ",", $flds);
//		$fldArr = explode(",", $flds);
//		$fieldList = array();
//		foreach ($fldArr as $fld){
//			if (!in_array($fld, array_keys($fileflds))){
//				$req = @$this->params[$fld]; 
//				if (is_array($req))
//					$req = implode(",", $req);
//				if($req == "")
//					$fieldList[$fld] = "0";
//				else
//					$fieldList[$fld] = stripslashes($req);
//			}
//		}
        if ($this->model->Insert()){
			$this->url->redirectAction('index');
		}
	//	if ($this->db->Insert($table, $fieldList)){
//			$this->url->redirectAction('index');
//		}
	//	else {
//			die("Can not create data: ".$this->db->ErrorMsg());
//		}        
        else
                {
                    die($this->model->error);
                }
     }
     public function editAction(){
     $table = "emp_type";
		$flds = "emptype_name,emptype_type";
		$pfld = "emptype_id";
		$quote = "'";
		$key = @$this->params["key"];    
        $modelType = new Models_EmpType($key);
		# show form to enter data
		$mainrs = $modelType->db->Execute("select * from ".$table." where ".$pfld."=".$quote.$key.$quote);
		$row = $mainrs->fields;
        
		foreach($row as $fld=>$val) {
			$row[$fld] = stripslashes($val);
		}   
		$this->tpl->assign('emptype', $row);
        $this->tpl->assign('listLink',$this->url->action('index'));
        return $this->view($modelType);
     }
     public function editPost(Models_EmpType $model)
     {
		if ($model->Update()){
			$this->url->redirectAction('index');
        }
        else {
                die("Can not update data: ".$this->model->error);
            }
     }
     
      function deleteAjax(){
         $model = new Models_EmpType();
        $ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				//if(!$this->db->Delete('emp_type', "emptype_id='$id'"))
                 if(!$model->Delete("emptype_id=$id"))
				{
					return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
					break;
				}
		}
		elseif ($ids != '')
		{
			//if(!$this->db->Delete('emp_type', "emptype_id='$ids'"))
            if(!$model->Delete("emptype_id=$ids"))
			{
				return json_encode(array('success' => false, 'msg' => $this->db->ErrorMsg()));
				break;
			}
		}
		return json_encode(array('success' => true));
    }
    
    
}// End Class


?>