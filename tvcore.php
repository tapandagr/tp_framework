<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/el/blog/nea-tis-epicheirisis/apli-adeia
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class Tvcore extends Module
{
    private $languages;

    protected static array $templates = [
        'admin_header' => '/themes/default/template/header.tpl',
    ];

    public function __construct()
    {
        $this->name = 'tvcore';
        $this->tab = 'administration';
        $this->version = '1.0.5';
        $this->author = 'tivuno.com';
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->displayName = $this->l('Core PrestaShop module - Cornelius');
        $this->description = $this->l('It adds useful hooks, functions and libraries to PrestaShop');
        $this->bootstrap = true;

        parent::__construct();

        $this->languages = Language::getLanguages();
    }

    public static function debugValue($string)
    {
        echo '<pre>' . $string . '</pre>';
    }

    protected static function installAdminHeaderTemplate(): bool
    {
        $handle = $result = @file(self::$templates['admin_header']);
        $i = 0;
        foreach ($handle as $line) {
            if (str_contains($line, '{* begin  HEADER *}')) {
                $result[$i] = "    {hook h='displayAdminAfterBodyOpeningTag'}" . PHP_EOL . $line;
                break;
            }
            ++$i;
        }
        file_put_contents(self::$templates['admin_header'], implode('', $result));
        @chmod(self::$templates['admin_header'], 0644);

        return true;
    }

    protected static function installModifiedTemplates(): bool
    {
        return self::installAdminHeaderTemplate();
    }

    protected static function uninstallModifiedTemplates(): bool
    {
        return self::uninstallAdminHeaderTemplate();
    }

    protected static function uninstallAdminHeaderTemplate(): bool
    {
        $handle = $result = @file(self::$templates['admin_header']);
        $i = 0;
        foreach ($handle as $line) {
            if (str_contains($line, 'displayAdminAfterBodyOpeningTag')) {
                unset($result[$i]);
                break;
            }
            ++$i;
        }
        file_put_contents(self::$templates['admin_header'], implode('', $result));
        @chmod(self::$templates['admin_header'], 0644);

        return true;
    }

    public function install(): bool
    {
        return parent::install() && self::registerHooks($this->name);
    }

    public function enable($force_all = false): bool
    {
        return parent::enable($force_all)
            && self::registerHooks($this->name)
            && self::installModifiedTemplates();
    }

    public function disable($force_all = false): bool
    {
        return parent::disable($force_all)
            && self::uninstallModifiedTemplates();
    }

    public static function registerHooks(string $module_dir): bool
    {
        $module_obj = Module::getInstanceByName($module_dir);
        if (Validate::isLoadedObject($module_obj)) {
            $file = _PS_MODULE_DIR_ . $module_dir . '/sql/hooks.php';
            if (is_file($file)) {
                $hooks = include_once $file;
                foreach ($hooks as $hook) {
                    $module_obj->registerHook($hook);
                }
            }
        }

        return true;
    }

    public function hookDisplayHeader(): void
    {
        $this->context->controller->registerStylesheet(
            'modules-tvcore-fontawesome',
            'modules/' . $this->name . '/libraries/fontawesome/css/all.min.css',
            ['media' => 'all', 'priority' => 150]
        );

        $this->context->controller->registerStylesheet(
            'modules-tvcore-main',
            'modules/' . $this->name . '/views/css/front/main.css',
            ['media' => 'all', 'priority' => 150]
        );

        $this->context->controller->registerStylesheet(
            'modules-tvcore-minimum',
            'modules/' . $this->name . '/views/css/front/minimum.css',
            ['media' => 'all', 'priority' => 150]
        );
        $this->context->controller->registerJavascript(
            'modules-tvcore-bootstrap',
            'modules/' . $this->name . '/views/js/front/bootstrap.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }

    public function hookDisplayAdminAfterBodyOpeningTag(): string
    {
        return $this->fetch('module:' . $this->name . '/views/templates/hooks/displayAdminAfterBodyOpeningTag.tpl');
    }

    public function hookDisplayAfterBodyOpeningTag(): string
    {
        return $this->fetch('module:' . $this->name . '/views/templates/hooks/displayAfterBodyOpeningTag.tpl');
    }

    public static function installSettings(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            if (isset($value['default_value'])) {
                Configuration::updateValue($key, $value['default_value']);
            }
        }

        return true;
    }

    public static function uninstallSettings(array $settings): bool
    {
        foreach ($settings as $key => $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    public function getSettingsWithValues(array $settings): array
    {
        $values = [];
        foreach ($settings as $setting) {
            $validation_method = $setting['validation'];
            if (isset($setting['language'])) {
                foreach (Language::getLanguages(false, false, true) as $language_id) {
                    $tmp_value = Configuration::get($setting['key'], $language_id);
                    if (Validate::$validation_method($tmp_value)) {
                        $values[$setting['key']][$language_id] = $tmp_value;
                    }
                }
            } else {
                $tmp_value = Configuration::get($setting['key']);
                if (Validate::$validation_method($tmp_value)) {
                    $values[$setting['key']] = $tmp_value;
                }
            }
        }

        return $values;
    }

    public static function installTables(string $module_dir): bool
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/tables.php';
        if (is_file($file)) {
            $moduleTables = include_once $file;
            foreach ($moduleTables as $table_key => $table_key) {
                if (!Db::getInstance()->execute($moduleTables[$table_key])) {
                    return false;
                }
            }

            self::installTablesOverride($module_dir);

            // Some tables which belong to a module are useful only when another one is installed
            self::installTablesAdditional($module_dir);
        }

        return true;
    }

    public static function installTablesOverride(string $module_dir): bool
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/table_overrides.php';
        if (is_file($file)) {
            $sql = include_once $file;
            foreach ($sql as $s) {
                $results = Db::getInstance()->executeS($s['check']);

                // If the columns do not exist, add them
                if (!sizeof($results)) {
                    if (!Db::getInstance()->execute($s['add'])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function installTablesAdditional(string $module_dir): bool
    {
        $modules_tables_additional = self::getTablesAdditional($module_dir);
        if (sizeof($modules_tables_additional)) {
            foreach ($modules_tables_additional as $module_table) {
                $query = str_replace(['_DB_PREFIX_', '_MYSQL_ENGINE_'], [_DB_PREFIX_, _MYSQL_ENGINE_], $module_table);
                if (!Db::getInstance()->execute($query)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected static function getTablesAdditional(string $module_dir): array
    {
        $result = [];
        $additional_tables_directory = _PS_MODULE_DIR_ . $module_dir . '/sql/additional';
        if (is_dir($additional_tables_directory)) {
            require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreFile.php';
            $additional_tables_files = TvcoreFile::getDirectoryFiles($additional_tables_directory, ['json']);
            foreach ($additional_tables_files as $module_key => $tables_path) {
                if (Module::isEnabled($module_key)) {
                    $result = array_merge($result, TvcoreJson::getDataFromLocalFile($tables_path));
                }
            }
        }

        return $result;
    }

    public static function uninstallTables(string $module_dir): bool
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/tables.php';
        if (is_file($file)) {
            $moduleTables = include_once $file;
            foreach ($moduleTables as $table_key => $table_key) {
                if (!Db::getInstance()->execute('DROP TABLE IF EXISTS  `' . _DB_PREFIX_ . $table_key . '`')) {
                    return false;
                }
            }

            self::uninstallTablesOverride($module_dir);

            // Some tables which belong to a module are useful only when another one is installed
            self::uninstallTablesAdditional($module_dir);
        }

        return true;
    }

    public static function uninstallTablesOverride(string $module_dir): bool
    {
        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/table_overrides.php';
        if (is_file($file)) {
            $sql = include_once $file;
            foreach ($sql as $s) {
                $results = Db::getInstance()->executeS($s['check']);

                // If the columns do exist, drop them
                if (sizeof($results)) {
                    if (!Db::getInstance()->execute($s['drop'])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function uninstallTablesAdditional(string $module_dir): bool
    {
        $modules_tables_additional = self::getTablesAdditional($module_dir);
        if (sizeof($modules_tables_additional)) {
            foreach ($modules_tables_additional as $table_key => $table_key) {
                if (!Db::getInstance()->execute('DROP TABLE IF EXISTS  `' . _DB_PREFIX_ . $table_key . '`')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $module_dir
     * @return true
     */
    public static function importData(string $module_dir): bool
    {
        require_once _PS_MODULE_DIR_ . 'tvcore/models/TvcoreString.php';

        $file = _PS_MODULE_DIR_ . $module_dir . '/sql/data.json';
        if (is_file($file)) {
            $languages = Language::getLanguages(false);
            $tables = TvcoreJson::getDataFromLocalFile($file);
            foreach ($tables as $table) {
                $model_path = _PS_MODULE_DIR_ . $module_dir . '/models/' . $table['class'] . '.php';
                require_once $model_path;
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

    public static function installTabs(string $module_dir): bool
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

    public static function uninstallTabs(string $module_dir): bool
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

    public static function debug(array $array): void
    {
        echo '<pre>' . print_r($array, true) . '</pre>';
        exit(rand());
    }

    public function getContent(): string
    {
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $configValue = (string) Tools::getValue('tvimport_prod_link');

            if (empty($configValue) || !Validate::isUrl($configValue)) {
                $output = $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('tvimport_prod_link', $configValue);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        $token = Tools::hash('tvcore/cron');

        $this->context->smarty->assign([
            'add_index_cron' => $this->context->link->getModuleLink(
                'tvcore',
                'addindex',
                [
                    'token' => $token,
                    'module_name' => 'YOUR_MODULE_DIR',
                ],
            ),
            'cron_links' => Hook::exec(
                'displayConfigurationTvcoreGetCronLinks',
                [
                    'token' => $token,
                ],
                null,
                true,
                false,
                false,
                null,
                true
            ),
        ]);

        return $output . $this->displayForm() . $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    public function displayForm(): string
    {
        $form = [
            'form' => [
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Import module link'),
                        'name' => 'tvimport_prod_link',
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        $helper->fields_value['tvimport_prod_link'] = Tools::getValue('tvimport_prod_link', Configuration::get('tvimport_prod_link'));

        return $helper->generateForm([$form]);
    }
}
