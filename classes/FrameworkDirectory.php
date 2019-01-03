<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkDirectory extends ObjectModel
{
    public $id_tp_framework_directory;
    public $category_id;
    public $parent_id;
    public $position;
    public $files;
    public $directories;

    public static $definition = array(
        'table'		=> 'tp_framework_directory',
        'primary'	=> 'id_tp_framework_directory',
        'multilang'	=> false,
        'fields'	=> array(
            'category_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'parent_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'position' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'files' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'directories' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            )
        ),
    );

    /**
    *
    */
    public function getPath()
    {
        $result = '/'.$this->position;

        //var_dump($this);

        //echo 'Initial: '.$result.'<br>';

        //We initialize the variable $object in order to use it in the loop
        $object = new $this($this->id);

        /**
        * While parent directory is defined (not 0), we add its "position"
        * in the beginning of the path. When the loop stops working, we
        * execute the same scenario for categories this time.
        */
        while ($object->parent_id != 0) {
            //We get the parent object
            $object = new $this($object->parent_id);

            //We add the parent in the path before checking again
            $result = '/'.$object->position.$result;
        }

        $category = new FrameworkCategory($object->category_id);

        $result = $category->getPath().$result;

        return $result;
    }
}
