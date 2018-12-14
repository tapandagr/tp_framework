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

class FrameworkConvert
{
    /**
    * This function is not finished yet
    */
    public function convertColumnsToLang($object)
    {
        //We get the old column names
        $old_columns = $object->class->array->getColumnFromArray($object->toLang()->columns,0);

        //We convert them to csv
        $columns_csv = $this->listToCSV($old_columns,false);

        //We get the respective data
        $sql = $object->class->database->select($columns_csv,$object->toLang()->table);

        //We get the new column names
        $new_columns = $object->class->array->getColumnFromArray($object->toLang()->columns,1);

        //We convert them to csv
        $columns_csv = $this->listToCSV($new_columns,true,true);

        //We convert the data we got into csv
        $data_csv = $this->tableToCSV($sql, $old_columns,$object->languages);

        //Ready to upload
        $object->class->database->insert($object->toLang()->table.'_lang',$columns_csv,$data_csv);

        $object->class->database->drop($object->toLang()->drop);
    }

    /**
    * Table to csv conversion
    */
    public static function tableToCSV($sql, $fields, $languages = null)
    {
        $i = 0;
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
}
