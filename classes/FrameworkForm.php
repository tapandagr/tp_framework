<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkForm
{
    /**
    *
    */
    public function __construct()
    {
        //$this->fw = new tp_framework('Form');
    }

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
