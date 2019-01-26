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
    public function __construct()
    {
        //$this->fw = new tp_framework('Directory');
        $this->convert = new FrameworkConvert();

        $this->directory = new stdClass();
        $this->directory->module = _PS_MODULE_DIR_.'tp_framework';
        $this->directory->uploads = $this->directory->module.'/uploads';
        $this->directory->images = $this->directory->uploads.'/images';
    }

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

    /**
    *
    */
    public function moveFileToTemporaryDirectory($object, $date)
    {
        //We calculate the path
        $origin = $object->path.'/'.$object->old_name;

        $temporary_directory = $this->getTemporaryDirectory($date);

        $this->copyFile($origin, $temporary_directory, $object->new_name);

        if (is_dir($origin))
        {
            rmdir($origin);
        }

        return $temporary_directory.'/'.$object->new_name;
    }

    /**
    *
    */
    public function moveFileToDestination($object, $parent_id, $temporary_directory, $class = 'FrameworkCategory')
    {
        $new_parent = new $parent_class($parent_id);

        $new_parent->getPath();

        $this->copyFile($temporary_directory, $new_parent->path, $object->new_name, 1);

        if (is_dir($temporary_directory))
        {
            rmdir($temporary_directory);
        }

        return true;
    }

    /**
    * We create temporary directory (based on the employee ID
    * and the submission datetime) to move files smoothly
    */
    public function getTemporaryDirectory($date)
    {
        $hash = $this->convert->hash(Context::getContext()->employee->id.$date);

        $path = '/temporary/'.$hash;

        //If the directory does not exist, we do create interface
        if (!is_dir($this->directory->uploads.$path))
        {
            $this->makeDirectorySimple($this->directory->uploads.$path);
        }

        return $path;
    }

    /**
    *
    */
    public function copyFile($origin, $destination, $new_name, $to_destination = 0)
    {
        //We calculate the absolute paths
        $temporary = 1 - $to_destination;

        $absolute = new stdClass();
        $absolute->origin = $this->getAbsolutePath($origin, $to_destination);
        $absolute->destination = $this->getAbsolutePath($destination, $temporary);
        $absolute->new = $absolute->destination.'/'.$new_name;

        //File copy
        if (is_file($absolute->origin))
        {
            return rename($absolute->origin, $absolute->new);
        }

        echo $absolute->origin;

        if (is_dir($absolute->origin))
        {
            //If the file does not exist in the destination, we will create it
            if (!is_dir($absolute->destination))
            {
                mkdir($absolute->new);

                //Fix directory permissions
                chmod($absolute->new, 0755);
            }

            //We loop through the directory
            $this->copyFileRecursion($absolute->origin, $absolute->new);

            //When finished and the old directory is empty, we delete it
            rmdir($absolute->origin);
        }

        return true;
    }

    /**
    *
    */
    public function copyFileRecursion($origin, $destination)
    {
        //File copy
        if (is_file($origin))
        {
            return rename($origin, $destination);
        }

        if (is_dir($origin))
        {
            //If the file does not exist in the destination, we will create it
            if (!is_dir($destination))
            {
                mkdir($destination);

                //Fix directory permissions
                chmod($destination, 0755);
            }

            //We read the category contents
            $directory = dir($origin);

            while (false !== $entry = $directory->read())
            {
                //We skip pointers
                if (in_array($entry, array('.', '..')))
                {
                    continue;
                }

                $new_origin = $origin.'/'.$entry;
                $new_destination = $destination.'/'.$entry;

                //Recursion
                $this->copyFileRecursion($new_origin, $new_destination);
            }

            //Clean up
            $directory->close();
        }

        return true;
    }

    /**
    *
    */
    public function getAbsolutePath($path, $temporary = 0)
    {
        if ($temporary == 0)
        {
            return $this->directory->images.$path;
        }

        return $this->directory->uploads.$path;
    }
}
