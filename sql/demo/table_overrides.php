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
        'check' => 'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'core_table` LIKE "our_new_column"',
        'add' => 'ALTER TABLE `' . _DB_PREFIX_ . 'core_table`
        add column `our_new_column` int(11) unsigned default 0 after `id_core_table`',
        'drop' => 'ALTER TABLE `' . _DB_PREFIX_ . 'core_table`
        drop column `our_new_column`',
    ],
];
