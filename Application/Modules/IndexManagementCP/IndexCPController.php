<?php
class IndexCPController extends Controller{
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	public function indexAction(){
        $model = new Models_IndexManagement();
        $this->tpl->parse('main.button');
        $this->tpl->assign('linkDel', $this->url->action('delete'));
		$this->tpl->assign('rightHeader', $this->renderAction(array('headerNotifyIcon', 'ComponentCP', 'ControlPanel')));
		return $this->view();
	}
    public function dataTableAjax(){
        $model = new Models_IndexManagement();
        $pageSize = @$this->params['iDisplayLength'];
        $offset = @$this->params['iDisplayStart'];

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
                $datas[$key]['modify'] = date("d-m-Y", strtotime($val['modified']));
                if($val['status'] == 0)
                    $datas[$key]['status'] = '<div class="createIndex btn" data-id="' . $val['id'] . '" data-url="' . $val['url'] . '" style="display:block;background:#f8a31f;color:#fff;">Tạo index</div>';
				else
                    $datas[$key]['status'] = '<div class="btn" data-id="' . $val['id'] . '" data-url="' . $val['url'] . '" style="display:block;background:#393;color:#fff;cursor: default;">Đã tạo index</div>';
				$datas[$key]['reset'] = '<a href="javascript:" class="createIndex" data-reset="true" data-id="' . $val['id'] . '" data-url="' . $val['url'] . '">Tạo lại index</a>';
            }
        }
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
        $data['aaData'] = $datas;
        return json_encode($data);
    }
}