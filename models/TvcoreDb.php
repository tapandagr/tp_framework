<?php

class TvcoreDb
{
    public static function execSql(string $file)
    {
        if (is_file($file)) {
            $sql = [];
            require_once $file;
            foreach ($sql as $s) {
                if (!Db::getInstance()->execute($s)) {
                    return;
                }
            }
        }
    }
}
