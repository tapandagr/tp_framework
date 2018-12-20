<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkForm
{
    /**
    *
    */
    public function getColumnRemainder($array)
    {
        $result = 0;
        for ($x=0; $x < count($array); $x++)
        {
            $result += $array[$x]['width'];
        }

        //We
        $result -= $array[$x - 1]['width'];

        return fmod($result,6);
    }
}
