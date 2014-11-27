<?php
    class galleryComponentController extends Presentation
    {
        public function __init()
        {
            $this->loadModule('GalleryCP');
        }

        public function showAlbumAction()
        {
            if(!empty($this->params['idAlbum']))
            {
                $models = new Models_ImageAlbum();
                $modelsImg = new Models_Images();
                $album = $models->db->select('id, name, desc')->where('id', $this->params['idAlbum'])->getcFields();
                $this->tpl->assign('album', $album);

                $listImg = $modelsImg->db->select('id, file_path')->where('album_id', $this->params['idAlbum'])->getcFieldsArray();
                foreach($listImg as $images)
                {
                    $this->tpl->insert_loop('main.images', 'images', $images);
                }
            }
            return $this->view();
        }

        public function boxVideoAction()
        {
            if(isset($this->params['videoId']))
            {
                $video = Models_Videos::getById($this->params['videoId']);
                $this->tpl->assign('video', $video);
            }
            return $this->view();
        }
    }