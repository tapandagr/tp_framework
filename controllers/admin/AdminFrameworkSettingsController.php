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
        $content = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'tp_framework/views/templates/admin/settings/header_tabs.tpl');
        $header_tabs = $this->getHeaderTabs();
        $this->context->smarty->assign(array(
            'current_tab_level' => 3,
            'module_tab' => $this->getActiveTabs(),
            'header_tabs' => $header_tabs,
            'content' => $this->content// . $content
        ));

        $standard_options = array(
            'general' => array(
                'title' => $this->trans('Γενικές', array(), 'Modules.tp_framework.Admin'),
                'fields' => array(
                    'tp_framework_subdirectory_count' => array(
                        'title' => $this->trans('Πλήθος υποφακέλων', array(), 'Modules.tp_framework.Admin'),
                        'cast' => 'intval',
                        'desc' => $this->trans('Εξαιρούνται οι κατηγορίες τέκνα', array(), 'Modules.tp_framework.Admin'),
                        'type' => 'text',
                        'required' => true,
                        'validation' => 'isUnsignedId',
                    ),
                ),
                'submit' => array('title' => $this->trans('Ενημέρωση', array(), 'Modules.tp_framework.Admin'), 'class' => 'button'),
            )
        );

        $this->fields_options = $standard_options;

        return parent::initContent();
    }

    /**
    *
    */
    public function getHeaderTabs()
    {
        $result = array();

        $result[0]['meta_title'] = $this->trans('Γενικές', array(), 'Modules.tp_framework.Admin');
        $result[0]['active'] = 0;
        $result[0]['href'] = '';
        $result[0]['class'] = 'general';

        return $result;
    }

    /**
    *
    */
    public function getActiveTabs()
    {
        $result = new stdClass();

        if(!isset($this->action) or $this->action == '')
        {
            $result->header = 'general';
        }else
        {
            $result->header = '';
        }

        return $result;
    }
}
