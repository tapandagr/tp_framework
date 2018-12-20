<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class FrameworkCategory extends ObjectModel
{
    public $id_tp_framework_category;
    public $parent;
    public $link_rewrite;
    public $level;
    public $position;
    public $meta_title;

	public static $definition = array(
        'table'		=> 'tp_framework_category',
        'primary'	=> 'id_tp_framework_category',
        'multilang'	=> true,
        'fields'	=> array(
            'parent' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'link_rewrite' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isLinkRewrite',
                'required' => true
            ),
            'level' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'position' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'meta_title' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'lang' => true,
                'required' => true
            ),
        ),
    );

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
    public function getCategoriesTree($object, $level = null)
    {
        //Get the module categories table
        $table = $object->name.'_category';

        //Get the restriction text
        //$restriction = $this->getRestriction('`parent`','=',0);

        //Get the categories
        $sql = $object->class->database->selectLang('*', $table, $object->language->id, null, '`level` ASC,`parent` ASC,t.`id_'.$table.'` ASC');

        //Get the max level of categories depth
        $max_level = $object->class->database->getValue($table, 'level', '`level` desc');

        //Final result initialization
        $result = array();

        if(count($sql) > 0)
        {
            for ($x=0; $x < count($sql); $x++)
            {
                $result[$x] = $sql[$x];

                //Get the parents of the specific category
                $parents = $this->getParents($result[$x], $table, $max_level);

                //Put them in the table
                for ($p=0; $p < count($parents); $p++)
                {
                    $result[$x]['parent_'.$p] = $parents[$p];
                }
            }

            //We put it into separate for, because we need the outcome of the previous one
            for ($x=0; $x < count($sql); $x++)
            {
                $result[$x]['descendants'] = $this->getDescendants($table, $result, $x);
            }

            //Update the actual positions with the absolute ones
            $result = $object->class->array->updatePositions($table,$result);

            //We sort the results based on the `pos` field
            $result = $object->class->array->bubbleSort($result);
        }

        //Add images home directory
        array_unshift(
            $result,
            array(
                'id_'.$table => 0,
                'level' => 0,
                'parent' => 0,
                'meta_title' => $this->trans('Χωρίς γονέα',array(),'Modules.tp_framework.Admin')
            )
        );

        return $result;
    }
}
