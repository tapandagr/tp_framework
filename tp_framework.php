<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    1.0
 * @since      1.0
 */

//We call the default Hook class
//require_once _PS_CLASS_DIR_.'Hook.php';

require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkTab.php';
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkTable.php';

class tp_framework extends Module
{
    /**
    *
    */
    public function __construct()
    {
        $this->class = $this->getClasses();

        //$this->table = new FrameworkTable();

        $this->name = 'tp_framework';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'tapanda.gr';
		$this->ps_versions_compliancy = ['min' => '1.7','max' => _PS_VERSION_];
		$this->bootstrap = true;

        parent::__construct();

		$this->displayName = $this->trans('Σκελετός', array(), 'Modules.tp_framework.Admin');
		$this->description = $this->trans('Το απαιτούμενο πρόσθετο για να λειτουργούν τα υπόλοιπα πρόσθετα παραγωγής μας', array(), 'Modules.tp_framework.Admin');

        //Get the shop languages
        $this->languages = $this->getLanguages();
    }

    /**
    *
    */
    public function install()
    {
        return
        (
            parent::install() and
            $this->class->tab->installTabs($this) and
            $this->class->table->installTables($this) and
            $this->registerHook('displayBackOfficeHeader')
        );
    }

    /**
    *
    */
    public function uninstall()
    {
        return
        (
            parent::uninstall() and
            $this->class->tab->uninstallTabs($this) and
            $this->class->table->uninstallTables($this)
        );
    }

    /**
    *
    */
    public function getClasses($module = null)
    {
        $result = new stdClass();

        $result->tab = new FrameworkTab();
        $result->table = new FrameworkTable();

        return $result;
    }

    /**
    * Get the installed languages (false: all, true: active)
    */
    public function getLanguages($limit = false)
    {
        return Language::getLanguages($limit, $this->context->shop->id);
    }
}
