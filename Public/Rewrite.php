<?php
return array(
    'CMSAdmin\/login\/([\w-_]+)' => array(
                            'alias' => 'Modules',
                            'module' =>'ControlPanel',
                            'controller' => 'Index',
                            'action' => 'login',
                            'url' => 'CMSAdmin/login/{url}'),
    'CMSAdmin\/login' => array(
                            'alias' => 'Modules',
                            'module' =>'ControlPanel',
                            'controller' => 'Index',
                            'action' => 'login',
                            'url' => 'CMSAdmin/login'),
    'CMSAdmin\/logout' => array(
                            'alias' => 'Modules',
                            'module' =>'ControlPanel',
                            'controller' => 'Index',
                            'action' => 'logout',
                            'url' => 'CMSAdmin/logout'),
    'cp' => array(
                            'alias' => 'Modules',
                            'module' =>'ControlPanel',
                            'controller' => 'Index',
                            'action' => 'index',
                            'url' => 'cp'),

    'Trang-chu.html' => array(
                            'langcode' => 'vi-VN',
                            'module' => 'Index',
                            'controller' => 'Index',
                            'action' => 'index',
                            'url' => 'trang-chu.html')
);