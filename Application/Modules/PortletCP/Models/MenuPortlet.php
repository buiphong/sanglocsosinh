<?php
#[Table('menu_portlet')]
#[PrimaryKey('id')]
class Models_MenuPortlet extends PTModel
{
	public $id;

	public $portlet_id;

	public $menu_id;

	#[DataType('number')]
	public $orderno;

	public $container_id;

	public $skin;

	public $title;

	public $params;

	public $values;

    /**
     * Add portlet menu
     */
    public static function addPortlet($params)
    {
        $obj = self::getInstance();

        $data = array(
            'title' => $params['name'],
            'portlet_id' => $params['portletid'],
            'container_id' => $params['region'],
            'menu_id' => $params['itemid'],
            'lang_code' => @$_SESSION['langcode']
        );
        //order
        $order = $obj->db->select('max(orderno)')->where('container_id', $params['region'])
            ->where('menu_id', $params['itemid'])->getField();

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

    /**
     * Moveup portlet
     */
    public static function moveUp($portletId)
    {
        $obj = self::getInstance();
        $item = $obj->db->where('id', $portletId)->getFields();
        if (!empty($item['menu_id']))
        {
            $before = $obj->db->select('id,orderno')->where('orderno <', $item['orderno'])
                ->where('menu_id', $item['menu_id'])->where('container_id', $item['container_id'])
                ->orderby('orderno', 'desc')->getFields();
            if (!empty($before))
            {
                //Đổi thứ tự sắp xếp
                if ($obj->db->where('id', $portletId)->update(array('orderno' => ($item['orderno'] - 1))))
                {
                    if ($obj->db->where('id', $before['id'])->update(array('orderno' => $item['orderno'])))
                        return array('success' => true);
                }
                else
                    return array('success' => false, 'msg' => $obj->error);
            }
        }
        return array('success' => false);
    }

    public static function moveDown($pid)
    {
        $obj = self::getInstance();
        $item = $obj->db->where('id', $pid)->getFields();
        if (!empty($item['menu_id']))
        {
            $after = $obj->db->select('id,orderno')->where('orderno >', $item['orderno'])
                ->where('menu_id', $item['menu_id'])->where('container_id', $item['container_id'])
                ->orderby('orderno', 'asc')->getFields();
            if (!empty($after))
            {
                //Đổi thứ tự sắp xếp
                if ($obj->db->where('id', $pid)->update(array('orderno' => ($item['orderno'] + 1))))
                {
                    if ($obj->db->where('id', $after['id'])->update(array('orderno' => $item['orderno'])))
                        return array('success' => true);
                }
            }
        }
        return array('success' => false, 'msg' => $obj->db->error);
    }

    public static function remove($pid)
    {
        $obj = self::getInstance();
        //reset orderno
        $portlet = $obj->db->where('id', $pid)->getFields();
        //update orderno
        $obj->db->Execute("update menu_portlet set orderno=(orderno-1) where menu_id='".$portlet['menu_id']."'
						and container_id='".$portlet['container_id']."' and orderno>" . $portlet['orderno']);
        if ($obj->db->where('id', $pid)->Delete())
            return array('success' => true);
        else
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
            return $obj->db->select('menu_portlet.*,portlets.edit_view,portlets.module,portlets.controller,
            portlets.action,portlets.id as pid,portlets.title,portlets.values as pvalues')
                ->join('portlets', 'portlets.id=menu_portlet.portlet_id')->where('menu_portlet.id', $pid)->getFields();
        }
        return false;
    }

    /**
     * Get max order by region and menu
     */
    public static function getMaxOrderNoByRegion($menu, $region)
    {
        if($menu && $region)
        {
            $obj = self::getInstance();
            return $obj->db->select('max(orderno)')->where('menu_id', $menu)->where('container_id', $region)->getField();
        }
        return false;
    }
}