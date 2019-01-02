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
}
