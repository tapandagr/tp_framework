<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.3
 * @since      0.0.1
 */

//We call the default Hook class
require_once _PS_CLASS_DIR_.'Hook.php';

/**
* This class is related to any content species on the website. E.g. product
*/
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkEntity.php';

require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkArray.php';
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkConvert.php';

/**
* This class is related to database manipulation
*/
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkDatabase.php';

/**
* Hooks
*/
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkHook.php';

require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkTab.php';
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkTable.php';

class tp_framework extends Module
{
    /**
    *
    */
    public function __construct()
    {
        //Get the shop languages
        $this->languages = $this->getLanguages();

        //Get the module classes
        $this->class = $this->getClasses();

        $this->name = 'tp_framework';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'tapanda.gr';
		$this->ps_versions_compliancy = ['min' => '1.7','max' => _PS_VERSION_];
        $this->need_instance = 0;
		$this->bootstrap = true;

        parent::__construct();

		$this->displayName = $this->trans('Σκελετός', array(), 'Modules.tp_framework.Admin');
		$this->description = $this->trans('Το απαιτούμενο πρόσθετο για να λειτουργούν τα υπόλοιπα πρόσθετα παραγωγής μας', array(), 'Modules.tp_framework.Admin');
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
            $this->class->hook->installHooks($this) and
            $this->registerHook('displayBackOfficeHeader') and
            $this->class->convert->convertColumnsToLanguage($this)
        );
    }

    /**
    * We first convert the fields back to non language because otherwise we will get error that the requested table does not exist
    */
    public function uninstall()
    {
        return
        (
            parent::uninstall() and
            //$this->class->convert->convertColumnsFromLanguage($this) and
            $this->class->tab->uninstallTabs($this) and
            $this->class->table->uninstallTables($this)
        );
    }

    /**
    *
    */
    public function hookAdminHeaderTabs($params)
    {
        return $this->fetch('module:'.$this->name.'/views/templates/admin/settings/header_tabs.tpl');
    }

    /**
    *
    */
    public function getClasses($module = null)
    {
        $result = new stdClass();

        $result->array = new FrameworkArray();
        $result->convert = new FrameworkConvert();
        $result->database = new FrameworkDatabase();
        $result->tab = new FrameworkTab();
        $result->table = new FrameworkTable();

        return $result;
    }

    /**
    * Get the installed languages (false: all, true: active)
    */
    public static function getLanguages($limit = false)
    {
        return Language::getLanguages($limit, Context::getContext()->shop->id);
    }

    /**
    *
    */
    public function toLanguage()
    {
        $result = array();

        $result[0]['table'] = 'hook';
        $result[0]['columns'] = array(
            array('id_hook', 'id_hook'),
            array('title', 'meta_title'),
            array('description', 'meta_description')
        );

        return $result;
    }

    /**
    *
    */
    public function getHooks()
    {
        $result = array(
            'hookDisplayAdminHeaderTabs',
            'hookDisplayBackOfficeHeader'
        );

        return $result;
    }
}
