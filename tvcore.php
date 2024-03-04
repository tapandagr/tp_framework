<?php
/**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/1-basic-license
 */

//require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreDatetime.php';
//require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreDb.php';
//require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreFile.php';

class Tvcore extends Module
{
    public function __construct()
    {
        $this->name = 'tvcore';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'tivuno.com';
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->displayName = $this->l('Cornelius - Core PrestaShop module');
        $this->description = $this->l('It adds useful hooks, functions and libraries to PrestaShop');

        parent::__construct();
    }

    public function install()
    {
        return parent::install() && $this->registerHooks();
    }

    public function registerHooks()
    {
        $hooks = [
            'displayHeader',
        ];

        foreach ($hooks as $h) {
            $this->registerHook($h);
        }

        return true;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerStylesheet(
            'modules-tvcore-bootstrap',
            'modules/' . $this->name . '/views/css/front/bootstrap.css',
            ['media' => 'all', 'priority' => 150]
        );
        $this->context->controller->registerStylesheet(
            'modules-tvcore-main',
            'modules/' . $this->name . '/views/css/front/main.css',
            ['media' => 'all', 'priority' => 150]
        );
        $this->context->controller->registerJavascript(
            'modules-tvcore-bootstrap',
            'modules/' . $this->name . '/views/js/front/bootstrap.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }
}
