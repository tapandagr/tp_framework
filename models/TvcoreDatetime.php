<?php

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
