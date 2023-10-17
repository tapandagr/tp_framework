<?php
/**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/basic-license
 */

class Tvcore extends Module
{
    public $restriction = null;

    /**
     *
     */
    public function __construct()
    {
        $this->name = 'tvcore';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'tivuno.com';
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->displayName = $this->l('Cornelius - Core PrestaShop module');
        $this->description = $this->l(
            'It adds useful hooks and other capabilities to PrestaShop.'
        );

        parent::__construct();
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerStylesheet(
            'modules-searchbar',
            'modules/' . $this->name . '/views/css/front/bootstrap.css',
            ['media' => 'all', 'priority' => 150]
        );
        $this->context->controller->registerJavascript(
            'modules-searchbar',
            'modules/' . $this->name . '/views/js/front/bootstrap.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }
}
