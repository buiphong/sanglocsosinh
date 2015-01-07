<?php

class PortletHelper
{
    /**
     * Get portlet CP button
     */
    public static function getPortletCPButton($pid,$pNum,$pMax,$type='menu',$editView=0)
    {
        $html = '<div class="renderCP-portlet-border untreated" accesskey="'.$pid.'">
					<div class="top-btn">';
        if ($pMax > 1)
        {
            if ($pNum == 1)
                $html .= '<a data-type="'.$type.'" href="#" class="cp-region-btn movedown" data-id="'.$pid.'" title="MoveDown">&nbsp;</a>';
            elseif ($pNum < $pMax)
                $html .= '<a data-type="'.$type.'" href="#" class="cp-region-btn moveup" data-id="'.$pid.'" title="MoveUp">&nbsp;</a>
						<a data-type="'.$type.'" href="#" class="cp-region-btn movedown" data-id="'.$pid.'" title="MoveDown">&nbsp;</a>';
            elseif ($pNum == $pMax)
                $html .= '<a data-type="'.$type.'" href="#" class="cp-region-btn moveup" data-id="'.$pid.'" title="MoveUp">&nbsp;</a>';
        }
        $html .= '<a data-type="'.$type.'" href="#" class="cp-region-btn edit" data-id="'.$pid.'" title="Edit">&nbsp;</a>
				  <a data-type="'.$type.'" href="#" class="cp-region-btn remove" data-id="'.$pid.'" title="Remove">&nbsp;</a>';
        //Kiểm tra nếu cho phép edit file view
        if ($editView == 1)
            $html .= '<a data-type="'.$type.'" href="#" class="cp-region-btn edit-view-file" data-id="'.$pid.'" title="Edit view file">&nbsp;</a>';
        $html .= '</div></div>';
        return $html;
    }

    /**
     * get portlet view. Use for portlet has full info.
     * @params $portlet must be an array
     */
    public static function getPortletView($portlet = array())
    {
        if(!empty($portlet))
        {
            $params = array();
            if (!empty($portlet['params']))
            {
                $arr = explode('&', $portlet['params']);
                foreach ($arr as $item)
                {
                    $arr2 = explode('=', $item);
                    if (!empty($arr2[1]))
                    {
                        $params[$arr2[0]] = $arr2[1];
                    }
                }
            }
            //Render content main action
            $router = new Router();
            $router->alias = 'Portal';
            $router->module = $portlet['module'];
            $router->controller = $portlet['controller'];
            $router->action = $portlet['action'];
            $router->args = $params;
            //get portlet
            $controller = new Controller_Front($router);
            $params['loadTemplate'] = false;
            $params['skin'] = @$portlet['skin'];
            return $controller->dispatch($router, 'Action', $params);
        }
    }

    /**
     * return portlet's cp_content
     */
    public static function getPortletCPView($pId, $type = 'menu')
    {
        if($type == 'router')
        {
            $portlet = Models_RouterPortlet::getPortletInfo($pId);
            $pMax = Models_RouterPortlet::getMaxOrderNoByRegion($portlet['router_id'],$portlet['container_id']);
        }
        elseif($type == 'menu')
        {
            $portlet = Models_MenuPortlet::getPortletInfo($pId);
            $pMax = Models_MenuPortlet::getMaxOrderNoByRegion($portlet['menu_id'],$portlet['container_id']);
        }

        return PortletHelper::getPortletCPButton($pId, $portlet['orderno'], $pMax, $type, $portlet['edit_view']).
            PortletHelper::getPortletView($portlet);
    }
}