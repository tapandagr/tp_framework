<?php
/**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class TvcoreAddIndexModuleFrontController extends ModuleFrontController
{
    public function postProcess(): void
    {
        header('Content-Type: application/json');
        $result = [];
        if (_PS_MODE_DEV_) {
            $result['token'] = Tools::hash('tvcore/cron');
        }
        if (!Tools::isPHPCLI() && Tools::hash('tvcore/cron') != Tools::getValue('token')) {
            $result['message'] = $this->module->l('Not authorized to execute this command');
        } else {
            $module_name = Tools::getValue('module_name');
            $module = Module::getInstanceByName($module_name);
            if (Validate::isLoadedObject($module) && Module::isEnabled($module_name) || Tools::getValue('force') == 1) {
                require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreFile.php';
                $module_dir = _PS_MODULE_DIR_ . $module_name;
                $directories = TvcoreFile::getSubDirectories($module_dir);

                foreach ($directories as $dir) {
                    TvcoreFile::copy($module_dir, $dir, 'index.php');
                }

                $result['message'] = $this->module->l('The index.php has been copied');
            } else {
                $result['message'] = $this->module->l('The module has not been found');
            }
        }

        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
