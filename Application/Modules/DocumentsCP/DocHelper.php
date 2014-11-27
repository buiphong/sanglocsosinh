<?php
class DocHelper
{
    /**
     * Get link doc
     */
    public static function getLinkDoc($doc)
    {

        if(!is_array($doc))
        {
            $model = new Models_Documents();
            $doc = $model->db->select('id,title,category')->where('id', $doc)->getcFields();
        }
        if($doc)
        {
            $modelCat = new Models_DocumentsCategory();
            $cat = $modelCat->getCatParent($doc['category']);
            $params['catname'] = String::seo($cat['title']);
            $params['title'] = String::seo($doc['title']);
            $params['id'] = $doc['id'];
            $url = new Url();
            return $url->action('detail', 'Document', 'Document', $params);
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
            $params['ncatid'] = $cat['id'];
            $url = new Url();
            return $url->action('listNewsCat','NewsComponent', 'News', $params);
        }
        return false;
    }
}