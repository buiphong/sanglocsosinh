<?php
class AdvertiseComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule(array('AdvertiseCP'));
    }
	public function advertiseAction()
	{
		$model = new Models_AdsBanner();
        $display = (!empty($this->params['num']))?$this->params['num']:1;
        if(!empty($this->params['type']))
            $model->db->where('zone_id',$this->params['type']);
        $ad = $model->db->select('link,name,file_data')->where('status',1)->limit($display,0)->getFieldsArray();
        if(!empty($ad))
            foreach($ad as $key=>$val)
            {
                $val['class'] = ($key != 0)?' mt10':'';
                $this->tpl->insert_loop('main.advertise','advertise',$val);
            }
		return $this->view();
	}
}