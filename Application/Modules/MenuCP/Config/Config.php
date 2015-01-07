<?php
//config module menu
$linkType = array(
        'link' => array(
            'code' => 'link',
            'name' => 'Đường dẫn'
        ),
        'newsid' => array(
            'code' => 'newsid',
            'name' => 'Chi tiết tin',
            'module' => 'NewsCP',
            'controller' => 'NewsMenu',
            'action' => 'index'
        ),
        'ncatid' => array(
            'code' => 'ncatid',
            'name' => 'Danh mục tin',
            'module' => 'NewsCP',
            'controller' => 'NCatMenu',
            'action' => 'index'
        ),
        'pcatid' => array(
            'code' => 'pcatid',
            'name' => 'Danh mục sản phẩm',
            'module' => 'ProductCP',
            'controller' => 'PCatMenu',
            'action' => 'index'
        ),
        'productid' => array(
            'code' => 'productid',
            'name' => 'Chi tiết sản phẩm',
            'module' => 'ProductCP',
            'controller' => 'ProductMenu',
            'action' => 'index'
        ),
        'eccatid' => array(
            'code' => 'eccatid',
            'name' => 'Danh mục sản phẩm',
            'module' => 'ECProductCP',
            'controller' => 'ECCatMenu',
            'action' => 'index'
        )
    );