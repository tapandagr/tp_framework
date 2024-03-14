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

class TvcoreDatetime
{
    public static function getNow()
    {
        $now = new DateTime();

        return $now->format('Y-m-d H:i:s');
    }

    public static function getTimeDifference($finish_datetime, $start_datetime = false)
    {
        if ($start_datetime === false) {
            $start_datetime = self::getNow();
        }
        $start_obj = new DateTime($start_datetime);
        $diff_obj = $start_obj->diff(new DateTime($finish_datetime));

        return $diff_obj->format('%H:%I:%S');
    }
}
