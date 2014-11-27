<?php
/**
 * Created by PhpStorm.
 * User: buiph_000
 * Date: 9/15/14
 * Time: 11:25 AM
 */
class CounterCPController extends Controller
{
    public function __init()
    {
        $this->loadTemplate('Metronic');
        $this->loadLayout('index');
    }
}