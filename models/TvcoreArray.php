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
class TvcoreArray
{
    public static function arraySplice(&$array, $offset = 0, $length = 1): array
    {
        $return = array_slice($array, $offset, $length, true);

        foreach ($return as $key => $value) {
            unset($array[$key]);
        }

        return $return;
    }

    /**
     * It converts a nested array to flat, preserving the keys
     * @credits https://stackoverflow.com/a/16855432
     * @param array $data
     * @return array
     */
    public static function flatten(array $data): array
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($data));
        $results = [];
        foreach ($iterator as $key => $value) {
            $keys = [];
            for ($i = 0; $i < $iterator->getDepth(); ++$i) {
                $tmp_key = $iterator->getSubIterator($i)->key();
                if (!is_int($tmp_key)) {
                    $keys[] = $tmp_key;
                }
            }
            $keys[] = $value;
            $results[] = [
                'nested' => implode('.', $keys),
                'flat' => implode('_', $keys),
            ];
        }

        return $results;
    }

    public static function nested($array): void
    {
        $x = count($array) - 1;
        $temp = [];
        for ($i = $x; $i >= 0; --$i) {
            $temp = [$array[$i] => $temp];
        }
    }

    public static function getFlat(array $array): array
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        $results = [];
        foreach ($ritit as $ignored) {
            $path = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $key = $ritit->getSubIterator($depth)->key();
                if ($key == 'array') {
                    continue 2;
                } else {
                    if (is_int($key)) {
                        $key = $ritit->getSubIterator($depth)->current();
                    }

                    $path[] = $key;
                }
                // $ritit->getSubIterator($depth)->key();
            }
            $results[] = [
                'nested' => join('.', $path),
                'flat' => join('_', $path),
            ];
        }

        return $results;
    }

    public static function addRow(array &$array, $new_record, int $position): void
    {
        $array = array_slice($array, 0, $position, true) +
            $new_record +
            array_slice($array, $position, count($array) - $position, true);
    }
}
