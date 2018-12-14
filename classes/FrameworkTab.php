<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 *
 * This class has been built to let us automate any procedure is related to admin tabs
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkTab
{
    /**
    * It installs a single tab
    */
    public function installTab($object,$class_name,$parent_class)
    {
        $tab = new $this();
        $tab->class_name = $class_name;
        $tab->module = $object->name;
        $tab->id_parent = Tab::getIdFromClassName($parent_class);
        foreach($this->languages as $l)
        {
            $tab->name[$l['id_lang']] = $object->displayName;
        }
        $tab->save();
    }

    /**
    * It installs a bunch of tabs (using a separate file)
    */
    public function installTabs($initial_object,$fake_tab = null)
    {
        if($initial_object->name == 'tp_framework')
        {
            $object = new stdClass();
            $object->class_name = 'AdminFrameworkDashboard';
            $object->name = $initial_object->name;
            $object->display_name = $initial_object->displayName;

            $fake_tab = new stdClass();
            $fake_tab->class_name = "AdminFrameworkCategories";
            $fake_tab->display_name = $initial_object->l('Κατηγορίες');
        }

        if(is_object($fake_tab) === true)
        {
            $tab = new Tab();
            $tab->class_name = $fake_tab->class_name;
            $tab->module = $object->name;
            $tab->id_parent = -1;

            foreach($initial_object->languages as $l)
            {
                $tab->name[$l['id_lang']] = $fake_tab->display_name;
            }
            $tab->save();
        }

        //Parent tab creation
        $tab = new Tab();
        $tab->class_name = $object->class_name;
        $tab->module = $object->name;
        $tab->id_parent = 0;
        foreach($initial_object->languages as $l)
        {
            $tab->name[$l['id_lang']] = $object->display_name;
        }
        $tab->save();

        //Sub-tabs creation
        $parent = new Tab($tab->id);
        $this->installSubTabs($initial_object,$parent);

        return true;
    }

    /**
    * It installs the children of a tab (operating with "installTabs")
    */
    public function installSubTabs($initial_object,$parent)
    {
        require_once _PS_MODULE_DIR_.$parent->module.'/sql/install_tabs.php';
        foreach ($tabs as $tab)
        {
            $newtab = new Tab();
            $newtab->class_name = $tab['class_name'];
            $newtab->id_parent = $parent->id;
            $newtab->module = $parent->module;

            foreach ($initial_object->languages as $l)
            {
                $newtab->name[$l['id_lang']] = $initial_object->l($tab['name']);
            }

            $newtab->save();
        }
    }

    /**
    * It uninstall a single tab
    */
    public function uninstallTab($class_name)
    {
        $id = Tab::getIdFromClassName($class_name);
        $tab = new Tab($id);
        $tab->delete();

        return true;
    }

    /**
    * It uninstalls a bunch of tabs (using a separate file)
    * @var $object Object It is the main class object (to retrieve the module directory name)
    */
    public function uninstallTabs($initial_object)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$initial_object->name.'/sql/uninstall_tabs.php';
        foreach ($sql as $s)
        {
            if($s)
            {
                $tab = new Tab($s);
                $tab->delete();
            }
        }

        return true;
    }
}
