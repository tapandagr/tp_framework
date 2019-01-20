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

//Directory
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkDirectory.php';

//Database
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkDatabase.php';

//File
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkFile.php';

//Form
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkForm.php';

//Links
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkLink.php';

//Object
require_once _PS_MODULE_DIR_.'tp_framework/classes/FrameworkObject.php';

class tp_framework extends Module
{
    public $restriction = null;

    /**
    *
    */
    public function __construct($restriction = null)
    {
        $this->restriction = $restriction;
        $this->name = 'tp_framework';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'tapanda.gr';
		$this->ps_versions_compliancy = array(
            'min' => '1.7.5',
            'max' => _PS_VERSION_
        );
        $this->need_instance = 0;
		//$this->bootstrap = true;
        $this->controllers = array('convert');

        parent::__construct();

		$this->displayName = $this->trans('Σκελετός', array(), 'Modules.tp_framework.Admin');
		$this->description = $this->trans('Το απαιτούμενο πρόσθετο για να λειτουργούν τα υπόλοιπα πρόσθετα παραγωγής μας', array(), 'Modules.tp_framework.Admin');

        //Get the shop languages
        $this->languages = $this->getLanguages();

        //Get the current language
        $this->language = Context::getContext()->language;

        $this->getClasses();

        $this->getDirectories();

        $this->links = $this->class->link->getAdminLinks($this->getAdminControllers());
    }

    /**
    *
    */
    public function install()
    {
        $files = $this->getFilesToImport();

        return
        (
            parent::install() and
            $this->class->database->installTabs($this) and
            //$this->class->database->installTables($this) and
            $this->class->file->copyfiles($files, _PS_ADMIN_DIR_.'/themes/default/template')// and
            //$this->class->database->installHooks($this)// and
            //$this->class->convert->convertColumnsToLanguage($this)
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
            $this->class->database->uninstallTabs($this)// and
            //$this->class->database->uninstallTables($this)
        );
    }

    /**
    *
    */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/admin.css', 'all');
        $this->context->controller->addCSS($this->_path.'libraries/font-awesome/css/all.css', 'all');
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
        $classes = array(
            'Array',
            'Category',
            'Convert',
            'Database',
            'Directory',
            'File',
            'Form',
            'Link',
            'Object'
        );

        //We reverse it because we know the value but not the index
        $flip = array_flip($classes);

        if (isset($this->restriction) and $this->restriction !== null)
        {
            unset($classes[$flip[$this->restriction]]);
        }

        $this->class = new stdClass();

        foreach($classes as $c)
        {
            $lower = strtolower($c);
            $c = 'Framework'.$c;
            $this->class->{$lower} = new $c();

            //We assign the module name and the language in the separate classes to preserve the objects inheritance
        }

        return $this;
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
    public static function getAdminControllers()
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
        $this->directory = new stdClass();
        $this->directory->module = _PS_MODULE_DIR_.'tp_framework';
        $this->directory->uploads = $this->directory->module.'/uploads';
        $this->directory->images = $this->directory->uploads.'/images';
        $this->directory->templates = new stdClass();
        $this->directory->templates->plain = $this->directory->module.'/views/templates';
        $this->directory->templates->import = $this->directory->templates->plain.'/import';

        return $this;
    }

    /**
    *
    */
    public function getFilesToImport()
    {
        $result = array();

        $base = $this->directory->templates->import.'/';

        $result[0]['name'] = 'content.tpl';
        $result[0]['relative'] = 'files/content.tpl';
        $result[0]['absolute'] = $base.$result[0]['relative'];
        $result[0]['directories'] = array('modules', 'tp_framework', 'files');

        return $result;
    }
}
