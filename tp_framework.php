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

// This class is related to any content species on the website. E.g. product
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkEntity.php';

//Array
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkArray.php';

//Category
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkCategory.php';

//Convert
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkConvert.php';

//Database
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkDatabase.php';

//File
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkFile.php';

//Form
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkForm.php';

//Hooks
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkHook.php';

//Links
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkLink.php';

//Object
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkObject.php';

//Tabs
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkTab.php';

//Tables
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkTable.php';

class tp_framework extends Module
{
    /**
    *
    */
    public function __construct()
    {
        $this->name = 'tp_framework';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'tapanda.gr';
		$this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_
        );
        $this->need_instance = 0;
		$this->bootstrap = true;

        parent::__construct();

		$this->displayName = $this->trans('Σκελετός', array(), 'Modules.tp_framework.Admin');
		$this->description = $this->trans('Το απαιτούμενο πρόσθετο για να λειτουργούν τα υπόλοιπα πρόσθετα παραγωγής μας', array(), 'Modules.tp_framework.Admin');

        //Get the shop languages
        $this->languages = $this->getLanguages();

        //Get the current language
        $this->language = Context::getContext()->language;

        //Get the module classes
        $this->class = $this->getClasses();

        $this->links = $this->class->link->getAdminLinks($this->getAdminControllers());

        $this->directory = $this->getDirectories();
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
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/libraries/font-awesome/css/all.css', 'all');
        $this->context->controller->addJs($this->_path.'views/js/admin.js');
    }

    public function hookDisplayDashboardTop($params)
    {
        return $this->fetch('module:'.$this->name.'/views/templates/admin/settings/header.tpl');
    }

    /**
    *
    */
    public function getClasses()
    {
        $result = new stdClass();
        $result->link = new FrameworkLink();
/*
        $classes = array(
            'Array',
            'Category',
            'Convert',
            'Database',
            'File',
            'Form',
            'Hook',
            'Link',
            'Object',
            'Tab',
            'Table'
        );

        if (($key = array_search($class, $classes)) !== false)
        {
            unset($classes[$key]);
        }

        $result = new stdClass();

        foreach($classes as $class)
        {
            $lower = strtolower($class);
            $class = 'Framework'.$class;
            $result->{$lower} = new $class();
        }
*/
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
            'hookDisplayBackOfficeHeader'
        );

        return $result;
    }

    /**
    *
    */
    public function getAdminControllers()
    {
        $result = array(
            array(
                'admin',
                'categories',
                array(
                    'Add',
                    'Delete',
                    'GetTree',
                    'GetUpdateForm',
                    'Update',
                    'View'
                )
            ),
            array(
                'admin',
                'files',
                array(
                    'Add',
                    'View',
                    'Update',
                    'Delete'
                )
            )
        );

        return $result;
    }

    /**
    *
    */
    public function getDirectories()
    {
        $result = new stdClass();
        $result->module = _PS_MODULE_DIR_.'tp_framework';
        $result->uploads = $result->module.'/uploads';
        $result->images = $result->uploads.'/img';

        return $result;
    }

    /**
    *
    */
    public function getSettings($class = null)
    {
        $fw = new stdClass();

        $fw->name = 'tp_framework';

        //Get the shop languages
        $fw->languages = self::getLanguages();

        //Get the current language
        $fw->language = Context::getContext()->language;

        //Get the module classes
        $fw->class = self::getClasses($class);

        $fw->directory = self::getDirectories();

        return $fw;
    }
}
