<?php
class GalleryHelper
{
    public static function detailImgAlbumLink($album)
    {
        if(!is_array($album))
            $album = Models_ImageAlbum::getById($album);
        elseif(isset($album['id']) && !isset($album['name']))
            $album = Models_ImageAlbum::getById($album['id']);
        elseif(!isset($album['id']))
            return false;
        $url = new Url();
        return $url->action('albumDetail', 'GalleryComponent', 'Gallery', array(
            'id' => $album['id'], 'name' => String::seo($album['name'])));
    }

    public static function detailAlbumVideo($album)
    {
        if(!is_array($album))
            $album = Models_VideoAlbum::getById($album);
        elseif(isset($album['id']) && !isset($album['name']))
            $album = Models_VideoAlbum::getById($album['id']);
        elseif(!isset($album['id']))
            return false;
        $url = new Url();
        return $url->action('albumVideoDetail', 'GalleryComponent', 'Gallery', array(
            'id' => $album['id'], 'name' => String::seo($album['name'])));
    }

    public static function getLinkCategory($cat)
    {
        if(!is_array($cat))
            $cat = Models_ImgAlbumCategory::getById($cat);
        elseif(isset($cat['id']) && !isset($cat['name']))
            $cat = Models_ImgAlbumCategory::getById($cat['id']);
        elseif(!isset($cat['id']))
            return false;
        $url = new Url();
        return $url->action('listAlbum', 'GalleryComponent', 'Gallery', array(
            'catid' => $cat['id'], 'name' => String::seo($cat['name'])));
    }
}