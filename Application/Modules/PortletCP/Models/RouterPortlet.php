<?php
#[Table('router_portlet')]
#[PrimaryKey('id')]
class Models_RouterPortlet extends PTModel
{
	public $id;

	public $portlet_id;

    public $router_id;

	public $str_router;

	#[DataType('number')]
	public $orderno;

	public $container_id;

	public $skin;

	public $title;

	public $params;

	public $values;

    /**
     * Add portlet router
     */
    public static function addPortlet($params)
    {
        $obj = self::getInstance();
        $data = array(
            'title' => $params['name'],
            'portlet_id' => $params['portletid'],
            'container_id' => $params['region'],
            'str_router' => $params['itemid'],
            'lang_code' => @$_SESSION['langcode']
        );
        if(empty($params['routerid']) && $params['itemid'])
        {
            //Thực hiện thêm mới router
            $a = explode('/', $params['itemid']);
            $mr = new Models_Router();
            $mr->Insert(array('module' => $a[0], 'controller' => $a[1], 'action' => $a[2],
                'template' => $params['template'], 'layout' => $params['layout'],
                'create_time' => date('Y-m-d H:i:s'), 'lang_code' => @$_SESSION['langcode']));
            $data['router_id'] = $mr->db->InsertId();
        }
        elseif($params['routerid'])
            $data['router_id'] = $params['routerid'];
        //order
        $order = $obj->db->select('max(orderno)')->where('container_id', $params['region'])
            ->where('str_router', $params['itemid'])->getField();

        if ($order > 0)
            $data['orderno'] = $order + 1;
        else
            $data['orderno'] = 1;

        if ($obj->Insert($data)) {
            return array('success' => true, 'id' => $obj->db->InsertId());
        }
        else
            return array('success' => false, 'msg' => $obj->error);
    }

    public static function moveUp($pid)
    {
        $obj = self::getInstance();
        //Get old orderno
        $current = $obj->db->select('id,str_router,container_id,orderno')
            ->where('id', $pid)->getFields();
        if (!empty($current))
        {
            $item = $obj->db->select('id,orderno')->where('orderno <', ($current['orderno']))
                ->where('str_router', $current['str_router'])
                ->where('container_id', $current['container_id'])->orderby('orderno', 'desc')->getFields();
            if (!empty($item) || $current['orderno'] > 1)
            {
                //Đổi thứ tự sắp xếp
                if ($obj->db->where('id', $current['id'])->update(array('orderno' => ($current['orderno'] - 1))))
                {
                    $obj->db->where('id', $item['id'])->update(array('orderno' => $current['orderno']));
                    return array('success' => true);
                }
            }
        }
        return array('success' => false, 'msg' => $obj->error);
    }

    public static function moveDown($pid)
    {
        $obj = self::getInstance();
        //Get old orderno
        $current = $obj->db->select('id,str_router,container_id,orderno')
            ->where('id', $pid)->getFields();
        if (!empty($current))
        {
            $item = $obj->db->select('id,orderno')->where('orderno >', ($current['orderno']))
                ->where('str_router', $current['str_router'])
                ->where('container_id', $current['container_id'])->orderby('orderno','asc')->getFields();
            $totalPortlet = $obj->db->where('str_router', $current['str_router'])
                ->where('container_id', $current['container_id'])->count(true);
            if (!empty($item) || $current['orderno'] <= $totalPortlet);
            {
                //Change position
                if ($obj->db->where('id', $current['id'])->update(array('orderno' => ($current['orderno'] + 1))))
                {
                    if(!empty($item))
                        $obj->db->where('id', $item['id'])->update(array('orderno' => $current['orderno']));
                    return array('success' => true);
                }
            }
        }
        return array('success' => false, 'msg' => $obj->error);
    }

    public static function remove($pid)
    {
        $obj = self::getInstance();
        //reset orderno
        $portlet = $obj->db->select('str_router,container_id,orderno')
            ->where('id', $pid)->getFields();
        if ($obj->db->where('id', $pid)->Delete())
        {
            //update orderno
            $obj->db->Execute("update router_portlet set orderno=(orderno-1) where str_router='".$portlet['str_router']."'
					and container_id='".$portlet['container_id']."' and orderno>" . $portlet['orderno']);
            return array('success' => true);
        }
        return array('success' => false, 'msg' => $obj->db->error);
    }

    /**
     * Get portlet info
     */
    public static function getPortletInfo($pid)
    {
        if($pid)
        {
            $obj = self::getInstance();
            return $obj->db->select('router_portlet.*,portlets.edit_view,portlets.module,portlets.controller,
            portlets.action,portlets.id as pid,portlets.title,portlets.values as pvalues')
                ->join('portlets', 'portlets.id = router_portlet.portlet_id')->where('router_portlet.id', $pid)->getFields();
        }
        return false;
    }

    /**
     * Get max order by region and menu
     */
    public static function getMaxOrderNoByRegion($router, $region)
    {
        if($router && $region)
        {
            $obj = self::getInstance();
            return $obj->db->select('max(orderno)')->where('router_id', $router)->where('container_id', $region)->getField();
        }
        return false;
    }
}