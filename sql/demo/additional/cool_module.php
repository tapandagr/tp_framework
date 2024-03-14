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
// Additional tables if cool_module is enabled
$sql['table1'] = 'create table if not exists `' . _DB_PREFIX_ . 'table1` (
    `id_table1` int(11) unsigned auto_increment,
    /* your rest mysql query */
    primary key (`id_table1`)
) engine=' . _MYSQL_ENGINE_ . ' default charset=utf8';

$sql['table1_lang'] = 'create table if not exists `' . _DB_PREFIX_ . 'table1_lang` (
    `id_table1` int(11) unsigned not null,
    `id_lang` tinyint(1) unsigned not null,
    /* your rest mysql query */
    primary key (`id_table1`, `id_lang`)
) engine=' . _MYSQL_ENGINE_ . ' default charset=utf8';

return $sql;
