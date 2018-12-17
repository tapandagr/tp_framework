<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.3
 *
 * This class has been built to let us handle the module settings
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class AdminFrameworkSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'tp_framework/views/templates/admin/settings/header_tabs.tpl');
        $this->context->smarty->assign(array(
             'content' => $this->content . $content,
        ));
    }
}
