<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class FrameworkFile extends ObjectModel
{
    public $id_tp_framework_file;
    public $category;
    public $directory_id;
    public $link_rewrite;
    public $extension;
    public $video;

	public static $definition = array(
        'table'		=> 'tp_framework_file',
        'primary'	=> 'id_tp_framework_file',
        'multilang'	=> false,
        'fields'	=> array(
            'category' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true
            ),
            'directory_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ),
            'link_rewrite' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isLinkRewrite',
                'required' => true
            ),
            'extension' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isLinkRewrite',
                'required' => true
            ),
            'video' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isLinkRewrite'
            ),
        ),
    );

    /**
    * It creates a directory in the specified location.
    *
    * @param $object object Object instance, usually a category from a module
    *
    * @param $datetime varchar If set, it creates a folder in the home images directory
    * that is given the name after the current datetime converted in md5 (for duplication elimination).
    */
    public function makeDir($object)
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
    *
    */
    public function calculateDirectoryLocation($object)
    {
        //We keep the object ’link_rewrite’ because the object will be recycled
        $link_rewrite = $object->link_rewrite;

        $result = '';

        if($object->parent != 0)
        {
            while($object->parent != 0)
            {
                //Get parent object
                $object = new FrameworkCategory($object->parent);
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
    public function getPath()
    {
        //We get the directory object
        $directory = new FrameworkDirectory($this->directory_id);

        //We get the directory path
        $directory->path = $directory->getPath();

        $result = $directory->path.'/'.$this->link_rewrite.'.'.$this->extension;

        return $result;
    }

    /**
    *
    */
    public function getAbsolutePath()
    {
        $path = $this->getPath();
        return tp_framework::getDirectories()->images.$path;
    }

    /**
    *
    */
    public function copyFiles($files, $destination)
    {
        foreach ($files as $f) {
            $this->copyFile($f, $destination);
        }

        return true;
    }

    /**
    *
    */
    public function copyFile($file, $destination)
    {
        $target = $destination;
        foreach ($file['directories'] as $d) {
            $target = $target.'/'.$d;
            self::makeDirectory($target);
        }

        copy($file['absolute'], $target.'/'.$file['name']);
        chmod($target.'/'.$file['name'], 0755);

        return true;
    }

    /**
    *
    */
    public function makeDirectory($absolute)
    {
        if (!file_exists($absolute)) {
            mkdir($absolute, 0644, true);
            copy(_PS_MODULE_DIR_.'tp_framework/index.php', $absolute.'/index.php');
        }

        return true;
    }
}
