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

class FrameworkArray
{
    /**
    * It isolates a single column (with the respective values) from a given array
    */
    public static function getColumnFromArray($array,$index)
    {
        $result = array();

        for ($x=0; $x < count($array); $x++)
        {
            $result[$x] = $array[$x][$index];
        }

        return $result;
    }
}
