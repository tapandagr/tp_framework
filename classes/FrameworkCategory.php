<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class FrameworkCategory extends ObjectModel
{
    public $id_tp_framework_category;
    public $parent_id;
    public $link_rewrite;
    public $level;
    public $position;
    public $meta_title;

	public static $definition = array(
        'table'		=> 'tp_framework_category',
        'primary'	=> 'id_tp_framework_category',
        'multilang'	=> true,
        'fields'	=> array(
            'parent_id' => array(
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
    *
    */
    public function getRelativePath()
    {
        $object = new FrameworkCategory($this->id);

        //We keep the link_rewrite because the object will be recycled
        $link_rewrite = $object->link_rewrite;

        //Path initialization
        $path = '';

        while($object->parent_id != 0)
        {
            $object = new FrameworkCategory($object->parent_id);
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
            $result = FrameworkDatabase::getValue('position', $table, '`position` DESC', 'id_'.$table.' != '.(int)$object->id.' AND `parent_id` = "'.$object->parent_id.'"');
        }else
        {
            //Get the last position for files assigned to the library
            $result = FrameworkDatabase::getValue('position', $table, '`position` DESC', '`gallery_id` != '.(int)$object->id);
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

        $result[0] = $child['parent_id'];

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
        return FrameworkDatabase::getValue($table,'parent_id','`id_'.$table.'` = '.$row);
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

    /**
    *
    */
    public function getPath()
    {
        $result = '/'.$this->link_rewrite;

        $object = new $this($this->id);

        while ($object->parent_id != 0) {
            //We get the parent object
            $object = new FrameworkCategory($object->parent_id);

            $result = '/'.$object->link_rewrite.$result;
        }

        return $result;
    }
}
