<?php
class NewsHelper
{
    /**
     * Get link news
     */
    public static function getLinkNews($news)
    {

        if(!is_array($news))
        {
            $model = new Models_News();
            $news = $model->db->select('id,title,category_id')->where('id', $news)->getcFields();
        }
        if($news)
        {
            $modelCat = new Models_NewsCategory();
            $params = array();
            $arrCat = $modelCat->getCatParent($news['category_id']);
            $i = 1;
            foreach($arrCat as $c)
            {
                $params['catname' . $i] = $c['urlTitle'];
                $i++;
            }
            $params['name'] = String::seo($news['title']);
            $params['nid'] = $news['id'];
            $url = new Url();
            return $url->action('detailNews', 'NewsComponent', 'News', $params);
        }
        return false;
    }

    /**
     * Get link news category
     */
    public static function getLinkCat($cat)
    {
        $model = new Models_NewsCategory();
        if(!is_array($cat))
            $cat = $model->db->where('id', $cat)->getFields();
        if($cat)
        {
            $params = array();
            $arrCat = $model->getCatParent($cat['id']);
            $i = 1;
            foreach($arrCat as $c)
            {
                $params['catname' . $i] = $c['urlTitle'];
                $i++;
            }
            $params['catId'] = $cat['id'];
            $url = new Url();
            return $url->action('listNewsCat','NewsComponent', 'News', $params);
        }
        return false;
    }
}