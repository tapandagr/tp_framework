<?php
/**
 * Cornelius - Core PrestaShop module
 *
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
return [
    [
        'parent_class' => 'AdminParentAttributesGroups',
        'class_name' => 'AdminTvfeatureGroups',
        /*
         * In case there is no defined iso_code for any language, the default one will be used
         */
        'name' => [
            'en' => 'Tab name for English',
            'el' => 'Tab name for Greek',
        ],
    ],
];
