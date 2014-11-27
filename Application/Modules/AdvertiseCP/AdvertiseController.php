<?php
class AdvertiseController extends Presentation
{
	public function getAdsAjax()
	{
		$zoneIds = explode(',',@$this->params['zoneIds']);
        $model = new Models_AdsBanner();
		if (!empty($zoneIds)) {
            $data = array();
            foreach($zoneIds as $zid)
            {
                //Get all banner zone
                $banners = $model->db->where('zone_id', $zid)->where('status', 1)
                            ->orderby('orderno')->getcFieldsArray();
                $html = '';
                if ($banners)
                {
                    foreach ($banners as $banner)
                    {
                        switch ($banner['banner_type'])
                        {
                            case 'image':
                                if (!empty($banner['link']))
                                {
                                    $link = $this->url->action('clickLinkAds', array('id' => $banner['id'], 'url' => $banner['link']));
                                    $html .= '<a href="'.$link.'" target="_blank">' .
                                    '<img src="'.Url::getContentUrl($banner['file_data']).'" alt="'.$banner['name'].'" width="'.$banner['width'].'px" height="'.$banner['height'].'px"/></a>';
                                }
                                else
                                    $html .= '<img src="'.Url::getContentUrl($banner['file_data']).'" alt="'.$banner['name'].'" width="'.$banner['width'].'px" height="'.$banner['height'].'px"/>';
                                break;
                            case 'flash':
                                $html .= '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"
                                        width="'.$banner['width'].'" height="'.$banner['height'].'" id="mymoviename">
                                        <param name="movie" value="'.Url::getContentUrl($banner['file_data']).'" />
                                        <param name="quality" value="high" />
                                        <param name="bgcolor" value="#ffffff" />
                                        <embed src="'.Url::getContentUrl($banner['file_data']).'" quality="high" bgcolor="#ffffff" width="'.$banner['width'].'" height="'.$banner['height'].'"
                                        name="mymoviename" align="" type="application/x-shockwave-flash"
                                        pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>';
                                break;
                            case 'text':

                                break;
                        }
                    }
                }
                $data[$zid] = $html;
            }
            return json_encode(array('success' => true, 'data' => $data));
		}
		else
			return json_encode(array('success' => false, 'html' => '<div>Không tìm thấy ads_zone</div>'));
	}

    public function clickLinkAdsAction()
    {
        $bannerId = @$this->params['id'];
        $link = @$this->params['url'];
        if(!empty($bannerId))
        {
            //Update click for banner
            $model = new Models_AdsBanner();
            if(!empty($link))
            {
                //redirect ads link
                if(strpos($link, 'http://') === false)
                    $link = 'http://' . $link;
                header('location: ' . $link);
            }
        }
        else
            return false;
    }
}