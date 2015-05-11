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
            /*if(isset($this->params['videoId']))
            {
                $video = Models_Videos::getById($this->params['videoId']);
                $this->tpl->assign('video', $video);
            }*/
            $videos = Models_Videos::getVideosByAlbum('', 10);
            if($videos)
                foreach($videos as $video){
                    $this->tpl->insert_loop('main.video', 'video', $video);
                }
            return $this->view();
        }

        public function videoPlaylistAction(){
            $videos = Models_Videos::getVideosByAlbum('', 10);
            if($videos){
                header("Content-Type: application/rss+xml; charset=utf-8");
                $html = '<rss version="2.0" xmlns:jwplayer="http://rss.jwpcdn.com/"><channel>';
                foreach($videos as $video){
                    $html .= '<item><title>'.$video['name'].'</title><description>'.$video['desc'].'</description>
    <jwplayer:image>'.$video['img_path'].'</jwplayer:image><jwplayer:source file="'.$video['video_path'].'" /></item>';
                }
                $html .= '</channel></rss>';
                echo $html;
                die;
            }
            return false;
        }
    }