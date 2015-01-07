<?php
return array(
    'priority' => 2,
    'items' => array(
        '([\w-_]+)\/ia-(\d+)\/([\w-_]+)\.html' => array(
                                'module' =>'Gallery',
                                'controller' => 'GalleryComponent',
                                'action' => 'albumDetail',
                                'url' => '{catname}/ia-{nid}/{name}.html'),
        'lia-(\d+)\/([\w-_]+)\/trang-(\d+)\.html' => array(
            'module' =>'Gallery',
            'controller' => 'GalleryComponent',
            'action' => 'listAlbum',
            'url' => 'lia-{catid}/{name}/trang-{page}.html'),
        'lia-(\d+)\/([\w-_]+)\.html' => array(
            'module' =>'Gallery',
            'controller' => 'GalleryComponent',
            'action' => 'listAlbum',
            'url' => 'lia-{catid}/{name}.html'),
        'ia-(\d+)\/([\w-_]+)\.html' => array(
            'module' =>'Gallery',
            'controller' => 'GalleryComponent',
            'action' => 'albumDetail',
            'url' => 'ia-{id}/{name}.html'),

        'va-(\d+)\/([\w-_]+)\.html' => array(
            'module' =>'Gallery',
            'controller' => 'GalleryComponent',
            'action' => 'albumVideoDetail',
            'url' => 'va-{id}/{name}.html'),
    )
);