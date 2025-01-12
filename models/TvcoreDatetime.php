<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
// PrestaShop validator - Start
if (!defined('_PS_VERSION_')) {
    exit;
}
// PrestaShop validator - Finish
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
