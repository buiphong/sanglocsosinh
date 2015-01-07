<?php
class ImagesController extends Controller{
	/**
	 * Thêm mới ảnh vào album (ajax)
	 */
	public function uploadAjax(){
		if (!empty($this->params)) {
			$model = new Models_Images();
			$data = array(
				'create_time' => date('Y-m-d H:i:s'),
				'created_uid' => $_SESSION["vc_control_panel"]["system_userid"],
				'name' => $this->params['name'],
				'desc' => $this->params['desc'],
				'file_path' => $this->params['file_path'],
				'album_id' => $this->params['album_id']
			);
			if ($model->Insert($data)) {
				$data['id'] = $model->db->InsertId();
				return json_encode(array('success' => true, 'msg' => 'Thêm mới thành công', 'html' => $this->html->renderAction('showPicInList', array('pic_infor' => $data))));
			}
			else
				return json_encode(array('success' => false, 'msg' => $model->db->error));
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Dữ liệu chưa đúng'));
	}
	/**
	 * Lấy html hiển thị một ảnh ở list
	 */
	public function showPicInListAction(){
		if (!empty($this->params['pic_infor'])) {
			if (empty($this->params['pic_infor']['file_path_thumb'])) {
				$this->params['pic_infor']['file_path_thumb'] = $this->url->thumbnail($this->params['pic_infor']['file_path'], 200, 110);
			}
			$this->tpl->assign('editLink', $this->url->action('editPic', 'Images', array('id' => $this->params['pic_infor']['id'])));
			$this->tpl->assign('deleteLink', $this->url->action('deletePic', 'Images', array('id' => $this->params['pic_infor']['id'])));
			$this->tpl->assign('pic', $this->params['pic_infor']);
			return $this->view();
		}
		return false;
	}
	/**
	 * edit picture
	 */
	public function editPicAjax(){
		if ($this->params['id']) {
			//Lấy thông tin ảnh
			$model = new Models_Images($this->params['id']);
			$this->tpl->assign('formAction', $this->url->action('editPicPost'));
			$modelAlbum = new Models_ImageAlbum();
			$albums = $modelAlbum->getTreeAlbum(0, true);
			$this->tpl->assign('cmbAlbum', $this->html->genSelect('album_id', $albums, $model->album_id, '', '', array('class' => 'chosen-select'), 'Toàn bộ'));
			return json_encode(array('success' => true, 'html' => $this->view($model)));
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Không tìm thấy thông tin ảnh'));
		
	}
	public function editPicPostAjax(Models_Images $model){
		if ($model->Update()) {
			$data = array('id' => $model->id, 'name' => $model->name, 'file_path' => $model->file_path);
			return json_encode(array('success' => true, 'html' => $this->html->renderAction('showPicInList', array('pic_infor' => $data))));
		}
		else 
			return json_encode(array('success' => false, 'msg' => $model->db->error));
	}
	/**
	 * Xóa ảnh
	 */
	public function deletePicAjax(){
		$model = new Models_Images();
		if (!empty($this->params['id'])) {
			if ($model->db->where('id', $this->params['id'])->Delete())
				return json_encode(array('success' => true));
			else
				return json_encode(array('success' => false, 'msg' => $model->db->error));	
		}
		else
			return json_encode(array('success' => false, 'msg' => 'Không tìm thấy thông tin ảnh'));
	}
}