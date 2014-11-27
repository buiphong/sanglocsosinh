<?php
/**
 * Created by PhpStorm.
 * User: buiph_000
 * Date: 9/4/14
 * Time: 2:11 PM
 */
class SearchComponentController extends Presentation
{
    public function searchAction()
    {
        $lucene_index_path = ROOT_PATH . DIRECTORY_SEPARATOR . SEARCH_DIR;
        setlocale(LC_CTYPE, 'utf-8');
        $time = microtime(true);
        //Gán cho kiểu dữ liệu không phân biệt chữ hoa và chữ thường.
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive());
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');

        $index = Zend_Search_Lucene::open($lucene_index_path);

        $textSearch = strtolower(String::removeSign($this->params['key']));
        $listKeys = explode(' ', $textSearch);
        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $q1 = new Zend_Search_Lucene_Search_Query_Phrase();
        $q2 = new Zend_Search_Lucene_Search_Query_Phrase();
        $q3 = new Zend_Search_Lucene_Search_Query_Phrase();
        foreach($listKeys as $listKey){
            $q1->addTerm(new Zend_Search_Lucene_Index_Term($listKey, 'flattitle'));
            $q2->addTerm(new Zend_Search_Lucene_Index_Term($listKey, 'flatbrief'));
        }
        $query->addSubquery($q1);
        $query->addSubquery($q2);
        //Zend_Search_Lucene::setResultSetLimit(3);
        $hits  = $index->find($query);
        foreach($hits as $hit)
        {
            $n['title'] = $hit->title;
            $n['brief'] = @$hit->brief;
            $n['image_path'] = $hit->imagePath;
            //$n['href'] = $hit->href;
            $this->tpl->insert_loop('main.news', 'news', $n);
        }

        $this->tpl->assign('total', count($hits));
        $extime = microtime(true) - $time;
        $this->tpl->assign('searchTime', ($extime) . '(s)');

        return $this->view();
    }

    public function searchPost()
    {
        if(!empty($this->params))
            $this->url->redirectAction('search', $this->params);
    }
}