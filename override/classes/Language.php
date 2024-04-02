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

class Language extends LanguageCore
{
    public static function getSecondaryLanguages(bool $active = false, bool $id_shop = false, bool $ids_only = false)
    {
        $cached_file = _PS_MODULE_DIR_ . 'tvcore/cache/languages' .
            (int) $active . (int) $id_shop . (int) $ids_only . '.json';
        if (!is_file($cached_file)) {
            $result = [];
            $id_default = (int) Configuration::get('PS_LANG_DEFAULT');
            $languages = Language::getLanguages($active, $id_shop, $ids_only);

            if ($ids_only) {
                foreach ($languages as $id_lang) {
                    if ($id_lang != $id_default) {
                        $result[] = $id_lang;
                    }
                }
            } else {
                foreach ($languages as $l) {
                    if ($l['id_lang'] != $id_default) {
                        $result[] = $l;
                    }
                }
            }

            TvcoreFile::setJson($cached_file, $result);

            return $result;
        }

        return TvcoreFile::getJson($cached_file);
    }
}
