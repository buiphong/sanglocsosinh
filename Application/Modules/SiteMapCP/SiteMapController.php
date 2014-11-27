<?php
/**
 *
 */
class SiteMapController extends Controller
{
    public function createSiteMapAction()
    {
        $siteMap = new Sitemap();
        if(Helper::moduleExist('NewsCP'))
        {
            Helper::loadModule('NewsCP');
            //siteMap for news
            $items = Models_News::getListNewsByStatus(1);
            if($items)
            {
                foreach($items as $item)
                    $siteMap->addItem(NewsHelper::getLinkNews($item));
            }
            //siteMap for news_category
        }
        //siteMap for product
        if(Helper::moduleExist('ProductCP'))
        {
            Helper::loadModule('ProductCP');

        }

        //siteMap for menu
        if(Helper::moduleExist('MenuCP'))
        {
            Helper::loadModule('MenuCP');
            $items = Models_Menu::getMenuByStatus();
            if($items)
            {
                foreach($items as $item)
                    $siteMap->addItem(MenuHelper::getLink($item));
            }
        }
        $siteMap->setPath(ROOT_PATH);
        $siteMap->createSitemapIndex();
    }
}