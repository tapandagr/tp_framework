<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 */

$sql = array();

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hook_lang`(
	`id_hook` int(11) UNSIGNED NOT NULL,
	`id_lang` tinyint(3) UNSIGNED NOT NULL,
	`meta_title` varchar(70) NOT NULL,
	`meta_description` varchar(150) NOT NULL,
	PRIMARY KEY (`id_hook`,`id_lang`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tp_framework_category`(
	`id_tp_framework_category` int(11) UNSIGNED NOT NULL auto_increment,
	`level` int(11) UNSIGNED DEFAULT 0,
	`parent_id` int(11) UNSIGNED DEFAULT 0,
	`position` int(11) UNSIGNED DEFAULT 0,
	`nleft` int(11) UNSIGNED DEFAULT 0,
	`nright` int(11) UNSIGNED DEFAULT 0,
	`link_rewrite` varchar(150) NOT NULL,
	PRIMARY KEY (`id_tp_framework_category`),
	UNIQUE KEY `category` (`id_tp_framework_category`,`nleft`,`nright`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tp_framework_category_lang`(
	`id_tp_framework_category` int(11) UNSIGNED NOT NULL,
	`id_lang` tinyint(3) UNSIGNED NOT NULL,
	`meta_title` varchar(150) NOT NULL,
	PRIMARY KEY (`id_tp_framework_category`,`id_lang`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tp_framework_directory`(
	`id_tp_framework_directory` int(11) UNSIGNED NOT NULL auto_increment,
	`parent` int(11) UNSIGNED DEFAULT 0,
	`position` tinyint(1) DEFAULT 0,
	`files` smallint(4) DEFAULT 0,
	`directories` tinyint(1) DEFAULT 0,
	PRIMARY KEY (`id_tp_framework_directory`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tp_framework_entity` (
  `id_tp_framework_entity` tinyint(3) NOT NULL auto_increment,
  `class` varchar(150) DEFAULT NULL,
  `language` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_tp_framework_entity`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8' ;

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tp_framework_entity_lang` (
  `id_tp_framework_entity` tinyint(3) NOT NULL,
  `id_lang` tinyint(2) NOT NULL,
  `meta_title` varchar(150) NOT NULL,
  PRIMARY KEY (`id_tp_framework_entity`,`id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8' ;

/**
*
*/
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'tp_framework_file`(
	`id_tp_framework_file` int(11) UNSIGNED NOT NULL auto_increment,
	`category_id` int(11) UNSIGNED NOT NULL,
	`link_rewrite` varchar(150) NOT NULL,
	`extension` varchar(4) NOT NULL,
	`video` varchar(150) DEFAULT NULL,
	PRIMARY KEY (`id_tp_framework_file`),
	KEY `unique` (`link_rewrite`,`extension`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
