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

    /**
    * It creates a directory in the specified location.
    *
    * @param $object object Object instance, usually a category from a module
    *
    * @param $datetime varchar If set, it creates a folder in the home images directory
    * that is given the name after the current datetime converted in md5 (for duplication elimination).
    */
    public function makeDirectory($object)
    {
        $directory = self::getDirectories();

        $result = self::calculateDirectoryLocation($object);

        //$result = $object->location.'/'.$object->link;

        $path = $directory->images.$result;

        if(!file_exists($path))
        {
            mkdir($path, 0755, true);
            copy($directory->module.'/index.php',$path.'/index.php');
        }

        //Fix directory permissions
        chmod($path, 0755);

        return $result;
    }

    /**
    * This is a simple makeDir with absolute path (no object)
    */
    public function makeDirectorySimple($absolute)
    {
        if (!file_exists($absolute)) {
            mkdir($absolute, 0755, true);
            copy(_PS_MODULE_DIR_.'tp_framework/index.php', $absolute.'/index.php');
        }

        return true;
    }

    /**
    *
    */
    public function calculateDirectoryLocation($object)
    {
        //We keep the object ’link_rewrite’ because the object will be recycled
        $link_rewrite = $object->link_rewrite;

        $result = '';

        if($object->parent_id != 0)
        {
            while($object->parent_id != 0)
            {
                //Get parent object
                $object = new FrameworkCategory($object->parent_id);
                $result .= '/'.$object->link_rewrite;
            }
        }

        $result .= '/'.$link_rewrite;

        return $result;
    }

    /**
    *
    */
    public function getDirectories()
    {
        $result = new stdClass();
        $result->module = _PS_MODULE_DIR_.'tp_framework';
        $result->uploads = $result->module.'/uploads';
        $result->images = $result->uploads.'/images';

        return $result;
    }
}
