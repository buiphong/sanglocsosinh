<?php
class SearchNewsController extends Controller
{
	public function __init(){
		$this->checkPermission();
		$this->loadTemplate('Metronic');
	}
    /**
     * Tạo chỉ mục tìm kiếm
     */
    public function createIndexAction()
    {
        $lucene_index_path = ROOT_PATH . DIRECTORY_SEPARATOR . SEARCH_DIR;
        $model = new Models_News();
        $arrData = $model->db->select("id,title,url_title,brief,content,image_path,video_path,category_id,status,keywords,published_by,published_date,created_date,created_by,notes,editor_id,type_id,hascomment,rating,hits,lang_code,author_id,hasindex")
            ->where('status',1)->where('hasindex <>', 1)->getFieldsArray();
        if(!empty($arrData))
        {
            setlocale(LC_CTYPE, 'utf-8');
            set_include_path('library');

            Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
            Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive());
            Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');


            if (file_exists($lucene_index_path."/segments")){
                $index = Zend_Search_Lucene::open($lucene_index_path);
            } else {
                $index = Zend_Search_Lucene::create($lucene_index_path);
            }

            Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding($index,'utf-8');
            $idArr = array();
            # indexing
            foreach ($arrData as $row)
            {
                $doc = new Zend_Search_Lucene_Document();

                if(base64_decode($row['brief'], true))
                    $row['brief'] = base64_decode($row['brief']);
                if(base64_decode($row['title'], true))
                    $row['title'] = base64_decode($row['title']);

                $doc->addField(Zend_Search_Lucene_Field::Keyword('newsid', $row["id"]));
                $doc->addField(Zend_Search_Lucene_Field::Text('flattitle', String::removeSign($row["title"]),'utf-8'));
                $doc->addField(Zend_Search_Lucene_Field::Text('title',strip_tags($row["title"]),'utf-8'));
                $doc->addField(Zend_Search_Lucene_Field::text('url_title', $row["url_title"]));
                $doc->addField(Zend_Search_Lucene_Field::Text('imagePath', $row["image_path"], 'utf-8'));
                $doc->addField(Zend_Search_Lucene_Field::Text('brief', $row["brief"], 'utf-8'));
                $doc->addField(Zend_Search_Lucene_Field::Text('flatbrief', String::removeSign(strip_tags(htmlspecialchars_decode($row["brief"]))), 'utf-8'));
                $doc->addField(Zend_Search_Lucene_Field::Text('publishedDate', strtotime($row["published_date"])));
                $doc->addField(Zend_Search_Lucene_Field::Text('hascomment', $row["hascomment"]));
                $doc->addField(Zend_Search_Lucene_Field::Text('langCode', $row["lang_code"]));
                # add to database
                $index->addDocument($doc);
                $idArr[] = $row["id"];
            }//
            //update index status for news
            $sql = "UPDATE news SET hasindex=1 WHERE id IN ('".implode("','", $idArr)."')";
            $model->db->Execute($sql);
            //Product
            $doc = $index->commit();
        }
		
		$this->tpl->assign('homeCPLink', $this->url->action('index', 'Index', 'ControlPanel'));
		$this->tpl->assign('listLink', $this->url->action("index", "NewsCP"));
		$this->tpl->assign('categoryLink', $this->url->action("index", "CategoryCP"));
		
        $this->tpl->assign('listProducts', count($arrData));
		return $this->view();
    }
    public function createIndexAjax()
    {
        $this->createIndexAction();
        $modelNews = new Models_News();
        $total = $modelNews->db->where('hasindex', 1)->count();
        return json_encode(array('success' => true, 'total' => $total));
    }

    public function emptyIndexDataAjax()
    {
        $files = VccDirectory::getFilesDir('SearchData');
        foreach($files as $f)
        {
            $f = Url::getAppDir() . 'SearchData' . DIRECTORY_SEPARATOR . $f;
            if(is_file($f))
            {
                if(!unlink($f))
                    return json_encode(array('success' => false));
            }
        }
        //Change index status news
        $modelNews = new Models_News();
        $modelNews->db->update(array('hasindex' => 0));
        return json_encode(array('success' => true));
    }
}






