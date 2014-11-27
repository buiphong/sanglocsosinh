<?php
class SliderComponentController extends Presentation
{
    public function __init()
    {
        $this->loadModule(array('SliderCP'));
    }
	public function showSliderAction()
	{
		$model = new Models_Slider();
        if(!empty($this->params['type']))
            $model->db->where('type_id', $this->params['type']);
		//lấy danh sách slider
		$sliders = $model->db->where('status',1)->getFieldsArray();
		foreach ($sliders as $slider){
			$this->tpl->insert_loop('main.slider', 'slider', $slider);
		}
		return $this->view();
	}
}