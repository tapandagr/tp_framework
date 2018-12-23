<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Free for personal use. No warranty. Contact us at info@tapanda.gr for details
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
        $sql = FrameworkDatabase::selectLang('*', $table, $object->language->id, null, '`level` ASC,`parent` ASC,t.`id_'.$table.'` ASC');

        //Get the max level of categories depth
        $max_level = FrameworkDatabase::getValue($table, 'level', '`level` desc');

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
                'parent' => 0,
                'meta_title' => Context::getContext()->getTranslator()->trans('Αρχική κατηγορία',array(),'Modules.tp_framework.Admin')
            )
        );

        return $result;
    }

    /**
    *
    */
    public function getRelativePath()
    {
        $object = new FrameworkCategory($this->id);

        //We keep the link_rewrite because the object will be recycled
        $link_rewrite = $object->link_rewrite;

        //Path initialization
        $path = '';

        while($object->parent != 0)
        {
            $object = new FrameworkCategory($object->parent);
            $path = '/'.$object->link_rewrite.$path;
        }

        $path = $path.'/'.$link_rewrite;

        return $path;
    }

    /**
    *
    */
    public function getLastPosition($fw, $table, $object, $increase = null)
    {
        if($table != 'tp_framework_gallery_content')
        {
            //Get the last position for the children of the parent
            $result = FrameworkDatabase::getValue($table, 'position', '`position` DESC', 'id_'.$table.' != '.(int)$object->id.' AND `parent` = "'.$object->parent.'"');
        }else
        {
            //Get the last position for files assigned to the library
            $result = FrameworkDatabase::getValue($table, 'position', '`position` DESC', '`gallery_id` != '.(int)$object->id);
        }

        if($increase != null)
            $result += 1;

        return $result;
    }

    /**
    *
    */
    public function getParents($child, $table, $max_level)
    {
        $result = [];

        $result[0] = $child['parent'];

        for ($x=1; $x < $max_level - 1; $x++)
        {
            if($result[$x-1] == 0)
                $result[$x] = 0;
            else
                $result[$x] = self::getParent($table, $result[$x-1]);
        }

        return $result;
    }

    /**
    *
    */
    public function getParent($table, $row)
    {
        return FrameworkDatabase::getValue($table,'parent','`id_'.$table.'` = '.$row);
    }

    /**
    *
    */
    public function getDescendants($table, $categories, $row)
    {
        $counter = new stdClass();
        $counter->categories = 0;
        $counter->children = 0;
        $result = [];

        while($counter->categories < count($categories))
        {
            //Match needle
            $found = false;

            //Parents needle
            $parent = 0;

            while(isset($categories[$counter->categories]['parent_'.$parent]) and $found === false)
            {
                //If found, we update the needle to force the loop stop
                if($categories[$row]['id_'.$table] == $categories[$counter->categories]['parent_'.$parent])
                    $found = true;

                $parent++;
            }

            if($found === true)
            {
                $result[$counter->children] = $categories[$counter->categories];
                $counter->children++;
            }

            $counter->categories++;
        }

        return $result;
    }
}
