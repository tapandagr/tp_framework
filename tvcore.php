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
    /**
     * @var array
     */
    private $languages;

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

        $this->languages = Language::getLanguages();
    }

    public function install()
    {
        return parent::install() && $this->registerHooks();
    }

    public function registerHooks()
    {
        $hooks = [
            'displayHeader',
            'displayAfterBodyOpeningTag',
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
        $this->context->controller->registerJavascript(
            'modules-tvcore-main',
            'modules/' . $this->name . '/views/js/front/main.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }

    public function hookDisplayAfterBodyOpeningTag()
    {
        return $this->fetch('module:' . $this->name . '/views/templates/hooks/displayAfterBodyOpeningTag.tpl');
    }

    public static function installTables(string $module_dir)
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/tables.php';
        if (is_file($file)) {
            $moduleTables = include_once $file;
            foreach ($moduleTables as $table_key => $table_key) {
                if (!Db::getInstance()->execute($moduleTables[$table_key])) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function uninstallTables(string $module_dir)
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/tables.php';
        if (is_file($file)) {
            $moduleTables = include_once $file;
            foreach ($moduleTables as $table_key => $table_key) {
                if (!Db::getInstance()->execute('DROP TABLE IF EXISTS  `' . _DB_PREFIX_ . $table_key . '`')) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function importData(string $module_dir)
    {
        require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreString.php';

        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/data.php';
        if (is_file($file)) {
            $languages = Language::getLanguages(false);
            $tables = include_once $file;
            //self::debug($tables);
            foreach ($tables as $table) {
                $model_path = _PS_MODULE_DIR_ . $module_dir . '/models/' . $table['class'] . '.php';
                include_once $model_path;
                $class = new $table['class']();
                $function = $table['function'];
                $column = $table['column'];
                foreach ($table['data'] as $datum) {
                    // We should check if the entity exists already
                    $id = $class::{$function}($datum[$column]);
                    if (!$id) {
                        foreach ($datum as $row_key => $row_value) {
                            if ($row_key != 'lang') {
                                $function_name = TvcoreString::setCamelFromSnake('set_' . $row_key);
                                $class->{$function_name}($row_value);
                            } else {
                                // The field usually contains more than one field
                                $language_columns = $row_value;
                                foreach ($language_columns as $language_column_key => $language_column_value) {
                                    $function_name = TvcoreString::setCamelFromSnake('set_' . $language_column_key);
                                    $language_column_data = [];
                                    foreach ($languages as $language) {
                                        if (isset($language_column_value[$language['iso_code']])) {
                                            $language_column_data[$language['id_lang']] = $language_column_value[$language['iso_code']];
                                        } else {
                                            $language_column_data[$language['id_lang']] = $language_column_value['en'];
                                        }
                                    }
                                    //self::debug($language_column_data);
                                    $class->{$function_name}($language_column_data);
                                }
                            }
                        }
                        $class->add();
                    }
                }
            }
        }

        return true;
    }

    public static function installTabs(string $module_dir)
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/tabs.php';
        if (is_file($file)) {
            $moduleTabs = include_once $file;
            $newTab = new Tab();
            foreach ($moduleTabs as $moduleTab) {
                $id_tab = $newTab::getIdFromClassName($moduleTab['class_name']);
                if (!$id_tab) {
                    $newTab->class_name = pSQL($moduleTab['class_name']);
                    $newTab->module = pSQL($module_dir);
                    $newTab->id_parent = 0;
                    if (isset($moduleTab['id_parent'])) {
                        $newTab->id_parent = (int) $moduleTab['id_parent'];
                    } elseif (isset($moduleTab['parent_class'])) {
                        $newTab->id_parent = (int) Tab::getIdFromClassName($moduleTab['parent_class']);
                    }

                    if (isset($moduleTab['level'])) {
                        $newTab->level = (int) $moduleTab['level'];
                    }

                    foreach (Language::getLanguages(false) as $language) {
                        if (isset($moduleTab['name'][$language['iso_code']])) {
                            $newTab->name[$language['id_lang']] = pSQL($moduleTab['name'][$language['iso_code']]);
                        }
                    }

                    $newTab->add();
                }
            }
        }

        return true;
    }

    public static function uninstallTabs(string $module_dir)
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/tabs.php';
        if (is_file($file)) {
            $moduleTabs = include_once $file;
            foreach ($moduleTabs as $moduleTab) {
                $id_tab = Tab::getIdFromClassName($moduleTab['class_name']);
                if ($id_tab) {
                    $tab = new Tab($id_tab);
                    $tab->delete();
                }
            }
        }

        return true;
    }

    public static function debug(array $array)
    {
        print('<pre>' . print_r($array, true) . '</pre>');
    }
}
