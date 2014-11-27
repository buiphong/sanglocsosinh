<?php
return array(
    'priority' => 0,
    'items' => array(
        'mt-([\w-_]+)\/([\w-_]+)\/([\w-_]+)\/([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => 'mt-{type}/{menu1}/{menu2}/{menu3}/{menu}.html'),
        'mt-([\w-_]+)\/([\w-_]+)\/([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => 'mt-{type}/{menu1}/{menu2}/{menu}.html'),
        'mt-([\w-_]+)\/([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => 'mt-{type}/{menu1}/{menu}.html'),
        'mt-([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => 'mt-{type}/{menu}.html'),
        '([\w-_]+)\/([\w-_]+)\/([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => '{menu1}/{menu2}/{menu3}/{menu}.html'),
        '([\w-_]+)\/([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => '{menu1}/{menu2}/{menu}.html'),
        '([\w-_]+)\/([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => '{menu1}/{menu}.html'),
        '([\w-_]+)\.html' => array(
                                'module' =>'Index',
                                'controller' => 'Index',
                                'action' => 'index',
                                'url' => '{menu}.html'))
);