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

class FrameworkDatabase
{
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
            $join = '`';
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

        $sql = 'SELECT '.$select.' FROM `'._DB_PREFIX_.$from.$join.$where.$order_by.$limit.$offset;

        $result = (db::getInstance())->executeS($sql);

        return $result;
    }

    /**
    *
    */
    //Update query
    public static function update($table,$fields,$csv,$column)
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.$table.'` '.$fields.' VALUES '.$csv.' ON DUPLICATE KEY UPDATE '.$column.' = VALUES('.$column.')';
        db::getInstance()->execute($sql);
    }

    /**
    * Delete query
    */
    public static function delete($table,$column,$operator,$value)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.$table.'` WHERE '.$column.' '.$operator.' '.$where;
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
}
