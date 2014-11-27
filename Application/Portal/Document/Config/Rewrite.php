<?php
return array(
    'priority' => 2,
    'items' => array(
        '([\w-_]+)\/([\w-_]+)-d(\d+)\.html' => array(
                                'module' =>'Document',
                                'controller' => 'Document',
                                'action' => 'detail',
                                'url' => '{catname}/{title}-d{id}.html'),

        '([\w-_]+)\/([\w-_]+)-dc(\d+)\/trang-(\d+)' => array(
                                'langcode' => 'vi-VN',
                                'module' => 'Document',
                                'controller' => 'Document',
                                'action' => 'listDoc',
                                'url' => '{catname1}/{catname2}-dc{catid}/trang-{page}'),
        '([\w-_]+)-dc(\d+)\/trang-(\d+)' => array(
                                'langcode' => 'vi-VN',
                                'module' => 'Document',
                                'controller' => 'Document',
                                'action' => 'listDoc',
                                'url' => '{catname}-dc{catid}/trang-{page}'),
        '([\w-_]+)\/([\w-_]+)-dc(\d+)' => array(
                                'langcode' => 'vi-VN',
                                'module' => 'Document',
                                'controller' => 'Document',
                                'action' => 'listDoc',
                                'url' => '{catname1}/{catname2}-dc{catid}'),
        '([\w-_]+)-dc(\d+)' => array(
                                'langcode' => 'vi-VN',
                                'module' => 'Document',
                                'controller' => 'Document',
                                'action' => 'listDoc',
                                'url' => '{catname}-dc{ncatid}')
        )
);