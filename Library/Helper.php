<?php
/**
 * Lớp helper - hỗ trợ xử lý
 * @author buiphong
 *
 */
class Helper
{
	/**
	 * Hỗ trợ tạo phân trang
	 * @param int $page
	 * @param int $pageSize
	 * @param int $totalRows
	 * @param string $class
	 */
	public static function pagging($page, $pageSize, $totalRows, $class='')
	{
		if (empty($page) || empty($totalRows))
		{
			return '';
		}
		
		if (empty($pageSize))
			$pageSize = 20;

		$pagecount = intval($totalRows/$pageSize); if (($totalRows%$pageSize)>0) $pagecount++;
		if ($page > $pagecount) $page = $pagecount;
		$offset = ($page -1) * $pageSize;
		if ($totalRows > 0)
			$from = $offset + 1;
		else
			$from = 0;
		
		if (($page * $pageSize) > $totalRows)
			$to = $totalRows;
		else
			$to = $page * $pageSize;
		$pageHtml = '';
		
		$pageHtml = '<div class="dataTables_info"><div class="left">Hiển thị <span id="showFrom">'.$from.'</span> - <span id="showTo">'.$to.'</span> của <span id="showTotal">'.$totalRows.'</span></div>
					</div>';
		$pageHtml .= '<div class="dataTables_paginate paging_full_numbers">';
		$before = '';
		$after = '';
		$start = 1;
		$end = $pagecount;
		if ($page > 4 && $page < $pagecount - 2)
		{
			$start = $page - 2;
			$end = $page + 2;
			$before = '<a tabindex="0" class="first paginate_button paginate_button_disabled '.$class.'" onclick="loadPage(this);" accesskey="1">Đầu</a>';
			$after = '<a tabindex="0" class="last paginate_button '.$class.'" onclick="loadPage(this);" accesskey="'.$pagecount.'">Cuối</a>';
		}
		elseif ($page <= $pagecount && $page >= $pagecount - 2 && $pagecount > 5)
		{
			$before = '<a tabindex="0" class="first paginate_button paginate_button_disabled '.$class.'" onclick="loadPage(this);" accesskey="1">Đầu</a>';
			$start = $pagecount - 5;
			$end = $pagecount;
		}
		elseif ($page <= 4 && $pagecount > 5)
		{
			$start = 1;
			$end = 5;
			$after = '<a tabindex="0" class="last paginate_button '.$class.'" onclick="loadPage(this);" accesskey="'.$pagecount.'">Cuối</a>';
		}
		if ($pagecount > 1)
		{
			$pageHtml .= $before;
			$pageHtml .= '<span>';
			for($i=$start; $i <= $end; $i++)
			{
				if ($i == $page)
					$class = 'paginate_active';
				else
					$class = 'paginate_button';
				$pageHtml .= '<a tabindex="0" class="'.$class.'" href="javascript:void(0);" onclick="loadPage(this);" accesskey="'.$i.'">'.$i.'</a>';
			}
			$pageHtml .= '</span>' . $after;
		}
		$pageHtml .= '</div>';
		
		return $pageHtml;
	}

    public static function getPaging($total, $pageSize, $page = 1, $setPageView = 2)
    {
        $pagecount = ceil($total/$pageSize);
        if ($page > $pagecount) $page = $pagecount;

        $arrPage = array();
        if ($pagecount > 1){
            //So sánh số trang hiện có so với số trang yêu cầu hiển thị
            if($pagecount <= $setPageView){
                $arrPage['start'] = 1;
                $arrPage['end'] = $pagecount;
            }
            else{
                $m = ceil($setPageView / 2);
                if($setPageView % 2 == 0){
                    $arrPage['end'] = $page + $setPageView /2 -1;
                    $arrPage['start'] = $page - $setPageView/2;
                }
                else{
                    $arrPage['end'] = $page + ($setPageView-1) /2;
                    $arrPage['start'] = $page - ($setPageView-1) /2;
                }
                if($page <= ceil($setPageView/2)){
                    $arrPage['end'] = $setPageView;
                    $arrPage['start'] = 1;
                }
                if($page >= $pagecount - $setPageView +1 && $page > $m){
                    $arrPage['end'] = $pagecount;
                    $arrPage['start'] = $pagecount - $setPageView +1;
                }
            }
        }
        return $arrPage;
    }
	
	/**
	 * Hỗ trợ tạo thư mục
	 * @param string $path
	 */
	public static function createDir($path){
		$cpath = "";
		$path = str_replace("\\", "/", $path);
		$pathArr = explode("/", $path);
		foreach($pathArr as $p){
			$cpath.="/".$p;
			if (!is_dir($cpath)) mkdir($cpath);
		}
		return true;
	}

    /**
     * Check có module hay không
     */
    public static function moduleExist($module)
    {
        $d = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR .
                $module;
        if(is_dir($d))
            return true;
        else
        {
            //Check trong portal
            if(is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Portal') &&
                is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Portal' . DIRECTORY_SEPARATOR .$module))
                return true;
        }
        return false;
    }

    public static function loadModule($modules)
    {
        if($modules)
        {
            $dirs = '';
            if(is_array($modules))
            {
                foreach($modules as $m)
                {
                    if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $m))
                        throw new Exception('Không tìm thấy module: ' . $m);
                    if(empty($dirs))
                        $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $m;
                    else
                        $dirs .= PATH_SEPARATOR . ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $m;
                }
            }
            elseif(!empty($modules))
            {
                if(!is_dir(ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $modules))
                    throw new Exception('Không tìm thấy module: ' . $modules);
                $dirs = ROOT_PATH . DIRECTORY_SEPARATOR . 'Application' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $modules;
            }
            $incPath = get_include_path();
            if(strpos($incPath, $dirs) === false)
                set_include_path($incPath . PATH_SEPARATOR . $dirs);
        }
        return false;
    }

    /**
     * Kiểm tra có đang cho phép edit layout không
     */
    public static function enableEditLayout()
    {
        if(isset($_SESSION['pt_control_panel']['editLayout']) && $_SESSION['pt_control_panel']['editLayout'])
            return true;
        return false;
    }

    /**
     * Kiểm tra đã login vào trang quản lý
     */
    public static function adminLoggedIn()
    {
        if(isset($_SESSION['pt_control_panel']))
        {
            //validate user
            return true;
        }
        return false;
    }

    /**
     * Check có phải là action sắp xếp bố cục hay không
     */
    public static function isRenderLayout(Router $router)
    {
        if($router->module == 'LayoutCP' && $router->controller == 'Render' && $router->action == 'index')
            return true;
        return false;
    }
}