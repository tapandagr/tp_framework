<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkEntity extends ObjectModel
{
    public $id_tp_framework_entity;
    public $language;
    public $class;
    public $meta_title;

    public static $definition = array(
        'table' => 'tp_framework_entity',
        'primary' => 'id_tp_framework_entity',
        'multilang' => true,
        'fields' => array(
            'language' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true
            ),
            'class' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 128
            ),
            // Lang fields
            'meta_title' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'size' => 128,
                'required' => true
            )
        ),
    );

    /**
    *
    */
    public function __construct()
    {
        //$this->fw = new tp_framework('Entity');
    }
}
