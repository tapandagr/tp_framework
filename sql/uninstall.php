<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'hook_lang`';
$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'tp_framework_category`';
$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'tp_framework_category_lang`';
$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'tp_framework_directory`';
$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'tp_framework_entity`';
$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'tp_framework_entity_lang`';
$sql[] = 'DROP TABLE IF EXISTS  `'._DB_PREFIX_.'tp_framework_file`';
