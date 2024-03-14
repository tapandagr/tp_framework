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
        // The respective class
        'class' => 'TvfeaturegroupsGroup',
        // The function which controls if we should add the record
        'function' => 'doesGroupExist',
        // Unique column
        'column' => 'reference',
        'data' => [
            [
                'reference' => 'misc',
                'position' => '1',
                /*
                 * Language data
                 * In case there is no defined iso_code for any language, the default one will be used
                 */
                'lang' => [
                    'name' => [
                        'en' => 'Misc',
                        'el' => 'Διάφορα',
                    ],
                ],
            ],
        ],
    ],
];
