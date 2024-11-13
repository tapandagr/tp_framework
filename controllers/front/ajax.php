<?php
/**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class TvcoreAjaxModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Tools::isPHPCLI() && Tools::hash('tvcore/cron') != Tools::getValue('token')) {
            exit('Not authorized');
        }

        $module_name = Tools::getValue('module_name');
        $method = Tools::getValue('method');
        $whitelisted_methods = [
            'refresh_cache' => 'refreshCache',
        ];

        $module = Module::getInstanceByName($module_name);

        if (Validate::isLoadedObject($module) && Module::isEnabled($module_name)) {
            require_once _PS_MODULE_DIR_ . $module_name . '/' . $module_name . '.php';
            if (isset($whitelisted_methods[$method])) {
                $method = $whitelisted_methods[$method];
                if (method_exists($module, $method)) {
                    exit($module->$method());
                }
            }

            exit('The function "' . $method . '" does not exist.');
        }

        exit('The module "' . $module_name . '" does not exist.');
    }
}
