<?php
return array(
    'priority' => 3,
    'items' => array(
        'giai-dap\/trang-(\d+)\.html' => array(
                                'module' =>'Faq',
                                'controller' => 'FaqComponent',
                                'action' => 'listFaq',
                                'url' => 'giai-dap/trang-{page}.html'),
        'giai-dap\/([\w-_]+)-(\d+)\/trang-(\d+)\.html' => array(
                                'module' =>'Faq',
                                'controller' => 'FaqComponent',
                                'action' => 'listFaq',
                                'url' => 'giai-dap/{catname}-{catid}/trang-{page}.html'),
        'giai-dap\/([\w-_]+)-(\d+)\.html' => array(
                                'module' =>'Faq',
                                'controller' => 'FaqComponent',
                                'action' => 'listFaq',
                                'url' => 'giai-dap/{catname}-{catid}.html'),
        'giai-dap.html' => array(
                                'module' =>'Faq',
                                'controller' => 'FaqComponent',
                                'action' => 'listFaq',
                                'url' => 'giai-dap.html')
    )
);