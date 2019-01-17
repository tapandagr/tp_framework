<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 *
 * This class has been built to let us automate any procedure is related to database tables
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkDatabase
{
    /**
    *
    */
    public function __construct()
    {
        $this->fw = new tp_framework('Database');
        //$this->fw->setClassRestriction('Database');
    }

    /**
    * CSV insert into table
    */
    public static function insert($table, $fields, $csv,$action = 'ignore',$on_duplicate = null)
    {
        if($action == 'ignore')
            $sql = 'INSERT IGNORE INTO '._DB_PREFIX_.$table.' '.$fields.' VALUES '.$csv;
        elseif($action == 'update')
            $sql = 'INSERT INTO '._DB_PREFIX_.$table.' '.$fields.' VALUES '.$csv.' ON DUPLICATE KEY UPDATE '.$on_duplicate;

        (db::getInstance())->execute($sql);
    }

    /**
    * General select query
    *
    * @param $select varchar We select all (*) or we restrict the data we are going to get back
    *
    * @param $from varchar The respective table that we will extract the data from
    * In complicated situations, we may join tables using this variable
    *
    * @param $where varchar If set, it narrows down the search given specific criteria
    *
    * @param $order_by varchar If set, it rearranges the display order
    *
    * @param $limit int If set, it makes the query return only the first X rows that match the criteria
    *
    * @param $offset int Useful for pagination, makes the query ignore the first Y rows
    *
    * @return Returns an array with rows that match the criteria that have been set
    */
    public static function select($select, $from, $join = null, $where = null, $order_by = null, $limit = null, $offset = null)
    {
        if($join === null)
            $join = '';
        else
            $join = ' '.$join;

        if($where === null)
            $where = '';
        else
            $where = ' WHERE '.$where;

        if($order_by === null)
            $order_by = '';
        else
            $order_by = ' ORDER BY '.$order_by;

        if($limit === null)
            $limit = '';
        else
            $limit = ' LIMIT '.$limit;

        if($offset === null)
            $offset = '';
        else
            $offset = ' OFFSET '.$offset;

        $sql = 'SELECT '.$select.' FROM `'._DB_PREFIX_.$from.'`';
        $sql .= $join;
        $sql .= $where;
        $sql .= $order_by;
        $sql .= $limit;
        $sql .= $offset;

        $result = (db::getInstance())->executeS($sql);

        return $result;
    }

    /**
    * It returns lang included queries
    *
    * @param
    */
    public static function selectLang($select, $table, $language, $restriction = null, $order_by = null, $limit = null, $offset = null)
    {
        if($restriction === null)
        {
            $restriction = '';
        }else {
            $restriction = ' AND '.$restriction;
        }

        //Debug
        $sql = self::select(
            $select,
            $table,
            ' t LEFT JOIN `'._DB_PREFIX_.$table.'_lang` tl ON tl.`id_'.$table.'` = t.`id_'.$table.'`',
            '`id_lang` = '.$language.$restriction,
            $order_by,
            $limit,
            $offset
        );

        //$sql = $this->selectQuery($select,$table.'` t LEFT JOIN `'._DB_PREFIX_.$table.'_lang` tl ON tl.`id_'.$table.'` = t.`id_'.$table.'`','`status` = 1 AND `id_lang` = '.$lid.$restriction,$order_by);

        return $sql;
    }

    /**
    *
    */
    public static function getValue($column, $table, $order_by = null, $where = null)
    {
        if($where === null)
            $where = '';
        else
            $where = ' WHERE '.$where;

        if($order_by === null)
            $order_by = '';
        else
            $order_by = ' ORDER BY '.$order_by;

        $sql = 'SELECT DISTINCT `'.$column.'` FROM `'._DB_PREFIX_.$table.'`'.$where.$order_by;

        return (db::getInstance())->getValue($sql);
    }

    /**
    * Update query
    */
    public static function update($table,$fields,$csv,$column)
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.$table.'` '.$fields.' VALUES '.$csv.' ON DUPLICATE KEY UPDATE '.$column.' = VALUES('.$column.')';
        db::getInstance()->execute($sql);
    }

    /**
    * Delete query
    */
    public static function delete($table, $column, $operator, $value)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.$table.'` WHERE '.$column.' '.$operator.' '.$value;
        db::getInstance()->execute($sql);
    }

    /**
    * Drop query
    */
    public static function drop($sql)
    {
        foreach($sql as $s)
        {
            if(!empty($s[1]))
            {
                //Drop entire table
                $result = 'DROP TABLE '._DB_PREFIX_.$s[0];
            }else
            {
                $result = 'ALTER TABLE '._DB_PREFIX_.$s[0].' ';
                foreach($s[1] as $column)
                {
                    $result .= 'DROP COLUMN `'.$column.'`,';
                }

                $result = rtrim($result, ',');
            }

            (db::getInstance())->execute($result);
        }
    }

    /**
    * It installs a single tab
    */
    public function installTab($object, $class_name, $parent_class)
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
    public function installTabs($module, $fake_tab = null)
    {
        if($module->name == 'tp_framework')
        {
            $object = new stdClass();
            $object->class_name = 'AdminFrameworkDashboard';
            $object->name = $module->name;
            $object->display_name = $module->displayName;

            $fake_tab = new stdClass();
            $fake_tab->class_name = "AdminFrameworkCategories";
            $fake_tab->display_name = $module->l('Κατηγορίες');
        }

        if(is_object($fake_tab) === true)
        {
            $tab = new Tab();
            $tab->class_name = $fake_tab->class_name;
            $tab->module = $object->name;
            $tab->id_parent = -1;

            foreach($module->languages as $l)
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
        foreach($module->languages as $l)
        {
            $tab->name[$l['id_lang']] = $object->display_name;
        }
        $tab->save();

        //Sub-tabs creation
        $this->installSubTabs($module, $tab);

        return true;
    }

    /**
    * It installs the children of a tab
    */
    public function installSubTabs($object, $parent)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$object->name.'/sql/install_tabs.php';

        foreach ($sql as $tab)
        {
            $newtab = new Tab();
            $newtab->class_name = $tab['class_name'];
            $newtab->id_parent = $parent->id;
            $newtab->module = $parent->module;

            foreach ($object->languages as $l)
            {
                $newtab->name[$l['id_lang']] = $object->l($tab['name']);
            }

            $newtab->save();
        }

        return true;
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
    public function uninstallTabs($module)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$module->name.'/sql/uninstall_tabs.php';
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

    /**
    *
    */
    public function installTables($module)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$module->name.'/sql/install.php';
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
    public function uninstallTables($module)
    {
        $sql = array();
        require_once _PS_MODULE_DIR_.$module->name.'/sql/uninstall.php';
        foreach ($sql as $s)
        {
            if (!Db::getInstance()->Execute($s))
                return false;
        }

        return true;
    }

    /**
    * It registers hooks (associated with the module) in case they do not already exist
    */
    public function installHooks($object)
    {
        foreach($object->getHooks() as $h)
        {
            $object->registerHook($h);
        }

        return true;
    }

    /**
    * It returns the current date & time based on the selected timezone
    */
    public function getDateTime($zone = 'Europe/Athens')
    {
        $timezone = new DateTimeZone($zone);
        $result = new DateTime(null, $timezone);
        return $result->format('Y-m-d H:i:s');
    }

    /**
    * Returns a categories tree from a module we specify
    *
    * @param $module varchar We have categories in many modules. This parameter helps us retrieve the desired ones
    *
    * @param $language int Language ID
    *
    * @param $level int Margin level / If set, the function will not return deeper children
    *
    * @return Returns a sorted array based on the position among the siblings and the parental hierarchy
    */
    public function getCategoriesTree($level = null, $restriction = null)
    {
        //Get the module categories table
        $table = $this->name.'_category';

        //Get the categories
        $sql = $this->fw->database->selectLang('*', $table, $this->language->id, $restriction, '`level` ASC,`parent_id` ASC,t.`id_'.$table.'` ASC');

        //Get the max level of categories depth
        $max_level = $this->fw->database->getValue('level', $table, '`level` desc');

        //Final result initialization
        $result = array();

        if(count($sql) > 0)
        {
            for ($x=0; $x < count($sql); $x++)
            {
                $result[$x] = $sql[$x];

                //Get the parents of the specific category
                $parents = FrameworkCategory::getParents($result[$x], $table, $max_level);

                //Put them in the table
                for ($p=0; $p < count($parents); $p++)
                {
                    $result[$x]['parent_'.$p] = $parents[$p];
                }
            }

            //We put it into separate for, because we need the outcome of the previous one
            for ($x=0; $x < count($sql); $x++)
            {
                $result[$x]['descendants'] = self::getDescendants($table, $result, $x);
            }

            //Update the actual positions with the absolute ones
            $result = FrameworkArray::updatePositions($table, $result);

            //We sort the results based on the `pos` field
            $result = FrameworkArray::bubbleSort($result);
        }

        //Add images home directory
        array_unshift(
            $result,
            array(
                'id_'.$table => 0,
                'level' => 0,
                'parent_id' => 0,
                'meta_title' => Context::getContext()->getTranslator()->trans('Αρχική κατηγορία',array(),'Modules.tp_framework.Admin'),
                'descendants' => array()
            )
        );

        return $result;
    }
}
