<?php
class PortletDefaultCPController extends Controller
{
	protected $ignoredParams = array('{appPath}','{title}','{description}','{keywords}');
	protected $ignoredTemplate = array('Metronic','admin');
	
	public function __init()
	{
		//$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
	
	/**
	 * Get portlet
	 */
	public function getDefaultPortletAction()
	{
		$template = @$this->params['template'];
		$layout = @$this->params['layout'];
		$region = @$this->params['region'];
		$page = @$this->params['page'];
		if (empty($page)) {
			$page = 1;
		}
		$pageSize = 2;
		$offset = ($page - 1) * $pageSize;
		//Select default portlet
		$model = new Models_DefaultPortlet();
		$modelPortlet = new Models_Portlet();
		if(!empty($this->params['search-text']))
		{
			$search = $this->params['search-text'];
			$this->tpl->assign('textSearch',$search);
			$portletId = $modelPortlet->db->select('id')->like('title',$search)->getFieldArray();
			if(!empty($portletId))
			{
				$portletId = implode(',', $portletId);	
				$model->db->where_in('portlet_id',$portletId);
			}
		}
		if (!empty($template)) {
			$model->db->where('template', $template);
            $this->tpl->assign('template', ' - ' . $template);
		}
		if (!empty($layout)) {
			$model->db->where('layout', $layout);
            $this->tpl->assign('layout', ' - ' . $layout);
		}
		if (!empty($region)) {
			$model->db->where('region', $region);
            $this->tpl->assign('region', ' - ' . $region);
		}
		$totalRows = $model->db->count();
		$portlets = $model->db->select('id,portlet_id,params,values,type,orderno')->orderby('orderno')
						->limit($pageSize, $offset)->getFieldsArray();
		if(!empty($portlets))
		{
			foreach ($portlets as $p)
			{
				$this->tpl->assign('editLink', $this->url->action('edit', array('id'=>$p['id'])));
				$p['name'] = $modelPortlet->db->select('title')->where('id',$p['portlet_id'])->getField();
				$this->tpl->insert_loop('main.portlet', 'portlet', $p);
			}
			$this->tpl->parse('main.button');
		}
		//$paramas = array("template"=>$this->params['template'],'layout'=>$this->params['template'],'region'=>$this->params['template']);
		$this->tpl->assign('form_action', $this->url->action('getDefaultPortlet'));
		$this->tpl->assign('createLink', $this->url->action('create', $this->params));

		if (!empty($this->params)) {
			$this->tpl->assign('createLink', $this->url->action('create', $this->params));
		}
		else 
			$this->tpl->assign('createLink', $this->url->action('create'));

		$this->tpl->assign('sidebarTemplate', $this->html->renderAction('getTreeTempRegion'));
		$this->tpl->assign('PAGE', Helper::pagging($page, $pageSize, $totalRows));
		return $this->view();
	}
	
	public function getDefaultPortletPost()
	{
		
	}
	public function editAction()
	{
		if(!empty($this->params['id']))
		{
			$id=$this->params['id'];
			$model = new Models_DefaultPortlet($id);
			//list portlet
			$modelPortlet = new Models_Portlet();
			$portlets = $modelPortlet->db->select('id,title')->orderby('title')->getFieldsArray();
			$this->tpl->assign('portlet_id', $this->html->genSelect('portlet_id', $portlets, $model->portlet_id, 'id', 'title',
					array('style' => 'width: 300px', 'class' => 'field')));
			//template
			$listTemplates = $this->getTemplates();
			$this->tpl->assign('template', $this->html->genSelect('template', $listTemplates,$model->template, 'name', 'name',
					array('style' => 'width: 300px', 'class' => 'field')));
			//layout
			$keys = array_keys($listTemplates);
			$layouts = $this->getLayout($keys[0]);
			$this->tpl->assign('layout', $this->html->genSelect('layout', $layouts,$model->layout,'name','name',
					array('style' => 'width: 300px', 'class' => 'field')));
			//region
			$keys = array_keys($layouts);
			$regions = $this->_analyseLayout($layouts[$keys[0]]['path']);
			foreach ($regions as $region)
			{
				$arrRegion[$region] = $region;
			}
			$this->tpl->assign('region', $this->html->genSelect('region', $arrRegion,$model->region,'', '',
					array('style' => 'width: 300px', 'class' => 'field')));
		
			$this->tpl->assign('form_action', $this->url->action('edit'));
			$paramas = array("template"=>$model->template,'layout'=>$model->layout,'region'=>$model->region);
			$this->tpl->assign('listLink', $this->url->action('getDefaultPortlet',$paramas));
			return $this->view($model);
		}
	}
	
	public function editPost(Models_DefaultPortlet $model)
	{
		if($model->Update())
		{
			$paramas = array("template"=>$model->template,'layout'=>$model->layout,'region'=>$model->region);
			$this->url->redirectAction('getDefaultPortlet',$paramas);
		}
		else
		{
			$this->showError('Query Error', $model->db->error);
		}
	}
	public function createAction()
	{
		$this->setView('edit');
		//list portlet
		$modelPortlet = new Models_Portlet();
		$portlets = $modelPortlet->db->select('id,title')->orderby('title')->getFieldsArray();
		$this->tpl->assign('portlet_id', $this->html->genSelect('portlet_id', $portlets, '', 'id', 'title',
				array('style' => 'width: 300px', 'class' => 'field')));
		//template
		$curT = (!empty($this->params['template']))?$this->params['template']:''; 
		$listTemplates = $this->getTemplates();
		$this->tpl->assign('template', $this->html->genSelect('template', $listTemplates,$curT, 'name', 'name',
				array('style' => 'width: 300px', 'class' => 'field')));
		//layout
		$keys = array_keys($listTemplates);
		$layouts = (!empty($curT)) ? $this->getLayout($curT) : $this->getLayout($keys[0]);
		$curL = (!empty($this->params['layout']))?$this->params['layout']:'';
		$this->tpl->assign('layout', $this->html->genSelect('layout', $layouts,$curL,'name', 'name',
				 array('style' => 'width: 300px', 'class' => 'field')));
		//region	
		$keys = array_keys($layouts);
		$regions = $this->_analyseLayout($layouts[$keys[0]]['path']);
		foreach ($regions as $region)
		{
			$arrRegion[$region] = $region;	
		}
		$curR = (!empty($this->params['region']))?$this->params['region']:'';
		$this->tpl->assign('region', $this->html->genSelect('region', $arrRegion,$curR,'', '',
				array('style' => 'width: 300px', 'class' => 'field')));
		
		$this->tpl->assign('form_action', $this->url->action('create'));
		$this->tpl->assign('listLink', $this->url->action('getDefaultPortlet',$this->params));
		return $this->view();
	}
	
	public function createPost(Models_DefaultPortlet $model)
	{
		if($model->Insert())
		{
			$paramas = array("template"=>$model->template,'layout'=>$model->layout,'region'=>$model->region);
			$this->url->redirectAction('getDefaultPortlet',$paramas);
		}
		else
		{
			$this->showError('Query Error', $model->db->error);
		}
	}
	
	/**
	 * Manager template - layout - region
	 */
	public function getTreeTempRegionAction()
	{
		//Lấy danh sách template
		$templates = $this->getTemplates();
		if ($templates) {
			foreach ($templates as $key => $temp)
			{
				//Get layout
				$layouts = $this->getLayout($temp['name']);
				if ($layouts) {
					foreach ($layouts as $k => $l)
					{
						$regions = $this->_analyseLayout($l['path']);
						if ($regions) {
							foreach ($regions as $r)
							{
								$a['name'] = $r;
								$a['pLink'] = $this->url->action('getDefaultPortlet', array('template' => $temp['name'],'layout' => $l['name'], 'region' => $r));
								$this->tpl->insert_loop('main.template.layout.lElement.region.rElement', 'rElement', $a);
							}
							$this->tpl->parse('main.template.layout.lElement.region');
						}
						$l['pLink'] = $this->url->action('getDefaultPortlet', array('template' => $temp['name'], 'layout' => $l['name']));
						$this->tpl->insert_loop('main.template.layout.lElement', 'lElement', $l);
					}
					$this->tpl->parse('main.template.layout');
				}
				$temp['pLink'] = $this->url->action('getDefaultPortlet', array('template' => $temp['name']));
				$this->tpl->insert_loop('main.template', 'template', $temp);
			}
			$this->tpl->parse('main.template');
		}
		$this->tpl->assign('listLink',$this->url->action('getDefaultPortlet'));
		$this->unloadLayout();
		return $this->view();
	}
	
	/**
	 * Lấy danh sách template
	 * @return array
	 */
	function getTemplates()
	{
		$ignoredDir = array('.', '..');
		$listDir = VccDirectory::getSubDirectories((Url::getAppDir().TEMPLATE_DIR));
		$a = array();
		foreach ($listDir as $key => $dir)
		{
			if (!in_array($key, $this->ignoredTemplate) && !in_array($key, $a)) {
				$a[$key] = array('name' => $key, 'path' => $dir);
			}
		}
		return $a;
	}
	
	/**
	 * lấy danh sách layout
	 */
	function getLayout($template)
	{
		//check dir
		if (!is_dir(Url::getAppDir(). TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template))
			return false;
		$list = scandir(Url::getAppDir().TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template . '/layout');
		$ignoredItem = array('.', '..','.svn');
		$arrItem = array();
		foreach ($list as $item)
		{
			if (!(array_search($item, $ignoredItem) > -1))
			{
				$item = substr($item, 0, -4);
				$arrItem[$item]['name'] = $item;
				$arrItem[$item]['path'] = Url::getAppDir().TEMPLATE_DIR .
					DIRECTORY_SEPARATOR . $template .DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . $item . '.htm';
			}
		}
		return $arrItem;
	}
	
	function getRegionAjax()
	{
		if (!empty($this->params['template']) && !empty($this->params['layout']))
		{
			$template = $this->params['template'];
			$layout = $this->params['layout'];
			$path = Url::getAppDir().TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template .
						DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . $layout. '.htm';
			$regions = $this->_analyseLayout($path);
			foreach ($regions as $region)
			{
				$arrRegion[$region] = $region;
			}
			if ($arrRegion)
				return json_encode(array('success' => true, 'html' => $this->html->genSelect('region',
						$arrRegion, '', '', '', array('style' => 'width: 300px', 'class' => 'field'))));
			else
				return json_encode(array('success' => false, 'msg' => 'Không tìm thấy region'));
		}
		return json_encode(array('success' => false, 'msg' => 'Không tìm thấy đường dẫn tới region'));
	}
	
	public function getLayoutAjax()
	{
		if (!empty($this->params['template']))
		{
			$layout = $this->getLayout($this->params['template']);
			if ($layout)
				return json_encode(array('success' => true, 'html' => $this->html->genSelect('layout',
						$layout, '', 'name', 'name', array('style' => 'width: 300px', 'class' => 'field'))));
			else
				return json_encode(array('success' => false, 'msg' => 'Không tìm thấy layout'));
		}
		return json_encode(array('success' => false, 'msg' => 'Không tìm thấy template'));
	}
	/**
	 * Phân tích layout lấy params
	 */
	function _analyseLayout($layout)
	{
		$content = file_get_contents($layout);
		$exp = "/\{([a-zA-Z0-9]*)\}/";
		preg_match_all($exp, $content, $arr);
		if (!is_array($arr)) return false;
		if (count($arr[0])==0) return false;
		foreach ($arr[0] as $key=>$value)
		{
			if (in_array($value, $this->ignoredParams))
				unset($arr[0][$key]);
		}
		return array_unique($arr[0]);
	}
	/**
	 * Delete
	 */
	public function deleteAjax()
	{
		$model = new Models_DefaultPortlet();
		$ids = $this->params['listid'];
		if (strpos($ids, ',') !== false)
		{
			$ids = explode(',', $ids);
			foreach ($ids as $id)
				if ($id != '')
				if(!$model->Delete("id=$id"))
				return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		elseif ($ids != '')
		{
			if(!$model->Delete("id=$ids"))
				return json_encode(array('success' => false, 'msg' => $this->ErrorMsg()));
		}
		return json_encode(array('success' => true, 'link' => $this->url->action('list')));
	}
}