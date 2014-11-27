<?php
class ErrorController extends Presentation
{
    public function error404Action()
    {
        $this->template = 'SLSS';
        $this->loadLayout('detail');
        return $this->view();
    }
}