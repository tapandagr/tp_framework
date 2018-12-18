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
}
