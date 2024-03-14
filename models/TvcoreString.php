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

class TvcoreString
{
    /**
     * It explodes string, but escapes separators that reside within parentheses or square brackets
     * As a fallback, in case the function does not produce an array, it explodes based on new line character.
     *
     * Example 1: QE65LST7TCUXXU\nQE75LST7TCUXXU
     * Example 2: Y21 Y22 QLED (eccetto Q80A, Q80B), AU8000~AU9000 43"~85", BU8000~BU9000 43"~85", QD OLED 55"~65"
     *
     * @param string $separator
     * @param string $string
     *
     * @return array
     */
    public static function explode(string $separator, string $string)
    {
        $result = self::explodeByNewLine($string);
        if (sizeof($result) == 1) {
            $result = self::explodeBySeparatorNotInParentheses($string, $separator);
            if (sizeof($result) == 1) {
                $result = self::explodeByMultipleSeparators($string);
            }
        }

        sort($result);

        return $result;
    }

    private static function explodeByNewLine(string $string)
    {
        $result = [];
        $explode = explode("\n", $string);
        foreach ($explode as $item) {
            $result[] = ltrim($item, '- ');
        }

        return $result;
    }

    private static function explodeBySeparatorNotInParentheses(string $string, string $separator)
    {
        $string = preg_replace('/(\([^)]*)' . $separator . '([^)]*\))/', '$1\\' . $separator . '$2', $string);
        $array = preg_split('~(?<!\\\)' . preg_quote($separator, '~') . '~', $string);

        $result = [];
        foreach ($array as $item) {
            $tmp = str_replace('\\', '', trim($item));
            $result[] = $tmp;
        }

        return $result;
    }

    private static function explodeByMultipleSeparators(string $string)
    {
        $result = [];
        $items = preg_split('/[,;|]/', $string, null, PREG_SPLIT_NO_EMPTY);
        foreach ($items as $item) {
            $result[] = trim($item);
        }

        return $result;
    }

    public static function getFloat($string)
    {
        $dotPos = strrpos($string, '.');
        $commaPos = strrpos($string, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
        if (!$sep) {
            return (float) preg_replace('/[^0-9]/', '', $string);
        }

        return (float) preg_replace('/[^0-9]/', '', substr($string, 0, $sep)) . '.' .
            preg_replace('/[^0-9]/', '', substr($string, $sep + 1, strlen($string)));
    }

    /**
     * It converts camel to snake
     * Example: setCamelFromSnake => set_camel_to_snake
     *
     * @credits https://gist.github.com/carousel/1aacbea013d230768b3dec1a14ce5751
     *
     * @param $input
     *
     * @return string
     */
    public static function setSnakeFromCamel($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * It converts snake to camel
     * Example: set_camel_to_snake => setCamelFromSnake
     *
     * @credits https://gist.github.com/carousel/1aacbea013d230768b3dec1a14ce5751
     * @param string $input
     * @return string
     */
    public static function setCamelFromSnake(string $input)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * It removes a part of the string from the right side (if exists)
     *
     * @credits https://stackoverflow.com/a/32739088
     * @param string $string
     * @param string $needle
     * @param bool $case_sensitive Performs case-sensitive matching, default to false
     * @return string
     */
    public static function rightTrim(string $string, string $needle, bool $case_sensitive = false)
    {
        $function = $case_sensitive ? 'strpos' : 'stripos';
        if ($function($string, $needle, strlen($string) - strlen($needle)) !== false) {
            $string = substr($string, 0, -strlen($needle));
        }

        return $string;
    }

    /**
     * @param $string
     * @param string $needle String to trim from the start
     * @param bool $case_sensitive Performs case-sensitive matching, default to false
     * @return string
     */
    public static function leftTrim($string, string $needle, bool $case_sensitive = false)
    {
        $function = $case_sensitive ? 'strpos' : 'stripos';
        if ($function($string, $needle) === 0) {
            $string = substr($string, strlen($needle));
        }

        return $string;
    }
}
