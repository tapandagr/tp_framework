<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.3
 * @since      0.0.1
 *
 * This class has been built to let us automate any procedure is related to database tables
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkConvert
{
    /**
    *
    */
    public function convertColumnsToLanguage($object)
    {
        $this->convertColumnsInit($object);

        return true;
    }

    /**
    *
    */
    public function convertColumnsFromLanguage($object)
    {
        $this->convertColumnsInit($object,1);

        return true;
    }

    /**
    *
    */
    public function convertColumnsInit($object, $from = 0)
    {
        $columns = new stdClass();
        $table = new stdClass();

        //We put != 1 instead of == 0 to protect our code against abusive behavior
        if($from != 1)
        {
            //Non language -> language
            $old = 0;
            $origin = '';
            $destination = '_lang';
        }else
        {
            //Language -> non language
            $old = 1;
            $origin = '_lang';
            $destination = '';
        }

        $new = 1 - $old;

        //We use "foreach" loop for future proof
        foreach($object->toLanguage() as $t)
        {
            //Variables set
            $table->old = $t['table'].$origin;
            $table->new = $t['table'].$destination;

            //We get the old column names
            $columns->old = $object->class->array->getColumnFromArray($t['columns'], $old);

            //We convert them to csv
            $columns->csv = $this->listToCSV($columns->old, false);

            //We get the respective data
            $columns->sql = $object->class->database->select($columns->csv, $table->old);

            //We get the new column names
            $columns->new = $object->class->array->getColumnFromArray($t['columns'], $new);

            //We convert them to csv
            $columns->csv = $this->listToCSV($columns->new,true,true);

            //We convert the data we got into csv
            $data = $this->tableToCSV($columns->sql, $columns->old, $object->languages);

            //Ready to upload
            $object->class->database->insert($table->new, $columns->csv, $data);

            //We delete the respective columns from old table
            //$object->class->database->drop($table->old, $columns->old);

        }

        return true;
    }

    /**
    * Table to csv conversion
    */
    public static function tableToCSV($sql, $fields, $languages = null)
    {
        $result = '';

        if($languages !== null && is_array($languages))
        {
            foreach($languages as $l)
            {
                foreach($sql as $s)
                {
                    $result .= '(';

                    for ($x=0; $x < count($fields); $x++)
                    {
                        if($x != 1)
                        {
                            $result .= '"'.addslashes($s[$fields[$x]]).'",';
                        }else
                        {
                            $result .= '"'.$l['id_lang'].'","'.addslashes($s[$fields[$x]]).'",';
                        }
                    }

                    //Last comma deletion
                    $result = rtrim($result, ",");

                    //Parenthesis add
                    $result .= '),';
                }
            }
        }else
        {
            foreach($sql as $s)
            {
                $result .= '(';

                for ($x=0; $x < count($fields); $x++)
                {
                    $result .= '"'.addslashes($s[$fields[$x]]).'",';
                }

                //Last comma deletion
                $result = rtrim($result, ",");

                //Parenthesis add
                $result .= '),';
            }
        }

        //Last comma deletion
        $result = rtrim($result, ",");

        return $result;
    }

    /**
    * List conversion to csv
    */
    public static function listToCSV($array,$parenthesis = true,$language = false)
    {
        if($parenthesis === true)
            $result = '(';
        else
            $result = '';

        for ($x=0; $x < count($array); $x++)
        {
            if($language === true and $x == 1)
                $result .= '`id_lang`,`'.$array[$x].'`,';
            else
                $result .= '`'.$array[$x].'`,';
        }

        $result = rtrim($result, ',');

        //Parenthesis add
        if($parenthesis === true)
            $result .= ')';

        return $result;
    }

    /**
    *
    */
    public function capital($string)
    {
        return ucfirst(strtolower($string));
    }

    /**
    *
    */
    public function lowercase($string)
    {
        return strtolower($string);
    }

    /**
    *
    */
    public function getPartOfString($string,$delimiter)
    {
        return substr($string,$delimiter);
    }

    /**
    *
    */
    public function makeArrayBySerializedData($input)
    {
        $result = [];

        $separate_inputs = explode('&',$input);

        for ($x=0; $x < count($separate_inputs); $x++)
        {
            //Second level explode to separate name and value
            $separate_name_value = explode('=',$separate_inputs[$x]);

            $brackets_explode = explode('[',$separate_name_value[0]);

            if(count($brackets_explode) == 1)
            {
                $result[$separate_name_value[0]] = $separate_name_value[1];
            }elseif(count($brackets_explode) == 2)
            {
                $actual_name = $brackets_explode[0];
                $result[$actual_name][$x] = $separate_name_value[1];
            }elseif(count($brackets_explode) == 3)
            {
                $actual_name = $brackets_explode[0];
                $result[$actual_name][rtrim($brackets_explode[1],']')][rtrim($brackets_explode[2],']')] = $separate_name_value[1];
            }elseif(count($brackets_explode) == 4)
            {
                $actual_name = $brackets_explode[0];
                $result[$actual_name][rtrim($brackets_explode[1],']')][rtrim($brackets_explode[2],']')][rtrim($brackets_explode[3],']')] = $separate_name_value[1];
            }
        }

        return $result;
    }
}
