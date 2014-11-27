<?php
class AttachController extends Controller
{
	private $uploadDir;
	private $convertType = array('doc','docx','xls','xlsx','DOC','DOCX','XLS','XLSX');
	function __init()
	{
		$this->uploadDir = 'media';
	}
	
	function adduploadmutilePost(){
		$filenames = array();
		foreach($this->getFilesUpload($_FILES['pictures']) as $file){
			$filenames[] = $this->upload_ad_post($file);
		}
		echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo 'var parDoc = window.parent.document;';
		$cid = $this->params['cvId'];
		$count = 0;
		foreach($filenames as $filename){
			if(is_array($filename)){
				$fieldLists = array();
				$fieldLists['attpath'] = $filename['namenew'];
				$fieldLists['file_name'] = $filename['name'];
				$fieldLists['cid'] = $cid;
				$fieldLists['created_date'] = date("Y-m-d H:i:s", time());
				//print_r($fieldLists);
				if ($this->db->Insert("attach_cv", $fieldLists)){
					$count++;
				}
			}
		}
		if($count > 0)
			echo "alert('Thêm file mới thành công');";
		echo "\n".'</script>';
		$totalRows = $this->db->GetFieldValue("select count(id) from `attach_cv` where cid='$cid'");
		print_r($totalRows);
		//$this->url->redirectAction('listUpload', 'Upload', 'candidates');
		exit();
	}
	function uploadmutilePost(){
		$filenames = array();
		$preview_url = '/modules/cvs/images';
                
		foreach($this->getFilesUpload($_FILES['pictures']) as $file){
			$filenames[] = $this->upload_ad_post($file);
		}
		echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo 'var parDoc = window.parent.document;';
		foreach($filenames as $filename){
			if(count($filename) > 0)
				echo "parent.$('#gallery_picture').append('<li><div><span>$filename[name]</span>  <img title=\"Xóa file\" class=\"delete\" src=\"$preview_url/icon_delete_small.gif\" onclick=\"deleteGallery($(this).parent().parent())\"><input type=\"hidden\" name=\"attpath[]\" value=\"$filename[namenew]|$filename[name]\" /></div></li>');";
		}
		echo "\n".'</script>';
		exit();
	}
	function uploadPost(){
		$filename = array();
		$preview_url = '/modules/cvs/images';
		
		foreach($this->getFilesUpload($_FILES['picture']) as $file){
			$filename[] = $this->upload_ad_post($file);
		}
		echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo 'var parDoc = window.parent.document;';
		foreach($filename as $filename){
			if($filename != '') {
				echo "parent.$('#gallery_picture').append('<li><div><span>$filename</span><img title=\"Xóa file\" class=\"delete\" src=\"$preview_url/icon_delete_small.gif\" onclick=\"deleteGallery($(this).parent().parent())\"><input type=\"hidden\" name=\"attpath[]\" value=\"$filename\" /></div></li>');";
			}
		}
		echo "\n".'</script>';
		exit();
	}
	function upload_ad_post($data) {
		if (strlen($data['name'])>4){
			$tempuploaddir = "media/";
			if(!is_dir($tempuploaddir)) mkdir($tempuploaddir,0755,true);
			$filetypes = $this->getFileExtension($data['name']);
			$filetype = strtolower($filetypes);

			if(($filetype != "txt")  && ($filetype != "xlsx")  && ($filetype != "docx")  && ($filetype != "xls")  && ($filetype != "doc")  && ($filetype != "pdf")  && ($filetype != "jpeg")  && ($filetype != "jpg") && ($filetype != "gif") && ($filetype != "png")){
				echo "<script>alert('File $data[name] không đúng định dạng');</script>";
				return;
			}
			
			$filename = $data['newName'];
			$tempfile = $tempuploaddir . "$filename";
			if (@file_exists($tempfile)) {
				echo "<script>alert('File $data[name] đã tồn tại');</script>";
				//return;
			}
			else if(is_uploaded_file($data['tmp_name'])){
				if (!copy($data['tmp_name'],"$tempfile")){
					unset($filename);
					unlink($tempfile);
					exit(); 
				}
				else
				{
					//Convert to pdf - phongbd
					if (in_array($filetype, $this->convertType))
					{
						//Thực hiện convert sang file pdf
						$currFile = $_SERVER['DOCUMENT_ROOT'] . "/$tempuploaddir" . $filename;
						$convertFile = $_SERVER['DOCUMENT_ROOT'] . "/$tempuploaddir" . $filename . '.pdf';
						exec('python /opt/openoffice.org3/program/DocumentConverter.py "'.$currFile.'" "'.$convertFile.'"');
					}
				}
			}
			
			return array('name'=>$data['name'],'namenew'=>$filename);   
		}
	}
	function getFilesUpload($files) {
		$j = 0;
		$return = array();
		for ($i = 0; $i < count($files['name']); $i++) {
			if (trim($files['name'][$i]) != '') {
				$return[$j]['name'] = trim($files['name'][$i]);
				$return[$j]['tmp_name'] = $files['tmp_name'][$i];
				$return[$j]['error'] = $files['error'][$i];
				$return[$j]['type'] = $files['type'][$i];
				$return[$j]['ext'] = strtolower(substr($files['name'][$i], strrpos($files['name'][$i], '.') + 1));
				$return[$j]['newName'] = microtime(true)."_".$this->char($files['name'][$i]);
				$j++;
			}
		}
		return $return;
	}
    function getFileExtension($str) {
        $i = strrpos($str,".");
        if (!$i) { return ""; }
        $l = strlen($str) - $i;
        $ext = substr($str,$i+1,$l);
        return $ext;
    }
	function deletefileAjax() {
		unlink('media/'.$_GET['file']);
		return;
    }
    function char($str) {
            $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|A)/", 'a', $str);
            $str = preg_replace("/(B)/", 'b', $str);
            $str = preg_replace("/(C)/", 'c', $str);
            $str = preg_replace("/(đ|Đ|D)/", 'd', $str);
            $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|E)/", 'e', $str);
            $str = preg_replace("/(F)/", 'f', $str);
            $str = preg_replace("/(G)/", 'g', $str);
            $str = preg_replace("/(H)/", 'h', $str);
            $str = preg_replace("/(ỉ|ị|ì|I|Ì|Ị|Í|í|Ỉ|ĩ|Ĩ)/", 'i', $str);
            $str = preg_replace("/(J)/", 'j', $str);
            $str = preg_replace("/(K)/", 'k', $str);
            $str = preg_replace("/(L)/", 'l', $str);
            $str = preg_replace("/(M)/", 'm', $str);
            $str = preg_replace("/(N)/", 'n', $str);
            $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|O)/", 'o', $str);
            $str = preg_replace("/(P)/", 'p', $str);
            $str = preg_replace("/(Q)/", 'q', $str);
            $str = preg_replace("/(R)/", 'r', $str);
            $str = preg_replace("/(S)/", 's', $str);
            $str = preg_replace("/(T)/", 't', $str);
            $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|U)/", 'u', $str);
            $str = preg_replace("/(V)/", 'v', $str);
            $str = preg_replace("/(W)/", 'w', $str);
            $str = preg_replace("/(X)/", 'x', $str);
            $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ|Y)/", 'y', $str);
            $str = preg_replace("/(Z)/", 'z', $str);

            $array = explode(" ", $str);
            $return = "";
            foreach($array as $value)
                    if($value != "")
                            $return = $return ."_". $value;
            return substr($return, 1);
    }
}