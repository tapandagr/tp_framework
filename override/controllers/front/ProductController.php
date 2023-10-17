<?php

class ProductController extends ProductControllerCore
{
    public function setMedia()
    {
        parent::setMedia();
        Hook::exec('actionFrontProductControllerSetMedia', []);
    }
}
