<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
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
    *
    */
    public function __construct()
    {
        $this->class = new stdClass();
        $this->class->directory = new FrameworkDirectory();
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
            $this->class->directory->makeDirectory($target);
        }

        copy($file['absolute'], $target.'/'.$file['name']);
        chmod($target.'/'.$file['name'], 0644);

        return true;
    }
}
