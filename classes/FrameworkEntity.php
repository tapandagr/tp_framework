<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 */

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
}
