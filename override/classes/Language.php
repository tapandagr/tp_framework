<?php

class Language extends LanguageCore
{
    /**
     * @param $active
     * @param $id_shop
     * @param $ids_only
     * @return array
     */
    public static function getSecondaryLanguages($active = true, $id_shop = false, $ids_only = false)
    {
        $cached_file = _PS_MODULE_DIR_ . 'tvcore/cache/languages' . (int) $active . (int) $id_shop . (int) $ids_only . '.json';
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
