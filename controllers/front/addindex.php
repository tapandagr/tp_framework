<?php
/**
 * Cornelius - Core PrestaShop module
 *
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class TvcoreAddIndexModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Tools::isPHPCLI() && Tools::hash('tvcore/cron') != Tools::getValue('token')) {
            exit('Not authorized');
        }

        $module_name = Tools::getValue('module_name');

        // echo '<br>' . Tools::hash('tvcore/cron');
        // echo '<br>' . Tools::getValue('token');

        $module = Module::getInstanceByName($module_name);

        if (Validate::isLoadedObject($module) && Module::isEnabled($module_name)) {
            require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreFile.php';
            $module_dir = _PS_MODULE_DIR_ . $module_name;
            $directories = TvcoreFile::getSubDirectories($module_dir);

            foreach ($directories as $dir) {
                TvcoreFile::copy($module_dir, $dir, 'index.php');
            }

            exit('The index.php has been copied');
        }
    }
}
