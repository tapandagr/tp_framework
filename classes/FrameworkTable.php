<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 *
 * This class has been built to let us automate any procedure is related to database tables
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkTable
{
    /**
    *
    */
    public function installTables($initial_object)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$initial_object->name.'/sql/install.php';
        foreach ($sql as $s)
        {
            if (!Db::getInstance()->Execute($s))
                return false;
        }

        return true;
    }

    /**
    *
    */
    public function uninstallTables($initial_object)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$initial_object->name.'/sql/uninstall.php';
        foreach ($sql as $s)
        {
            if (!Db::getInstance()->Execute($s))
                return false;
        }

        return true;
    }
}
