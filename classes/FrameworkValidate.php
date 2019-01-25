<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkValidate
{
    /**
    * It removes rows with empty or invalid values to prepare the form inputs for proper insert
    */
    public function removeUnwantedRows($result, $validate)
    {
        //FrameworkConvert::pre($result);
        $column_names = array_keys($result['data'][0]);

        for ($x = 0; $x < count($result['data']); $x++)
        {
            foreach ($column_names as $column)
            {
                if (is_array($result['data'][$x][$column]))
                {
                    foreach ($result['data'][$x][$column] as $field)
                    {
                        if ($field == '' or !Validate::{$validate['validateLang'][$column]}($field))
                        {
                            unset($result['data'][$x]);
                            break 2;
                        }
                    }
                } else
                {
                    echo '<br>Example: '.$result['data'][$x][$column];
                    if ($result['data'][$x][$column] == '' or !Validate::{$validate['validate'][$column]}($result['data'][$x][$column]))
                    {
                        unset($result['data'][$x]);
                        break 1;
                    }
                }
            }
        }

        return $result;
    }
}
