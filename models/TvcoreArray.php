<?php

class TvcoreArray
{
    public static function arraySplice(&$array, $offset = 0, $length = 1)
    {
        $return = array_slice($array, $offset, $length, true);

        foreach ($return as $key => $value) {
            unset($array[$key]);
        }

        return $return;
    }

    public static function flatten(array $array)
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        $results = [];
        foreach ($ritit as $value) {
            $path = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $key = $ritit->getSubIterator($depth)->key();
                if (is_int($key)) {
                    $key = $ritit->getSubIterator($depth)->current();
                }
                $path[] = $key;
            }

            $results[join('_', $path)] = $value;
        }

        return $results;
    }

    public static function nested($array)
    {
        $x = count($array) - 1;
        $temp = [];
        for ($i = $x; $i >= 0; $i--) {
            $temp = [$array[$i] => $temp];
        }
    }

    public static function getFlat(array $array)
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        $results = [];
        foreach ($ritit as $ignored) {
            $path = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $key = $ritit->getSubIterator($depth)->key();
                if (is_int($key)) {
                    $key = $ritit->getSubIterator($depth)->current();
                }
                $path[] = $key;//$ritit->getSubIterator($depth)->key();
            }
            $results[] = [
                'nested' => join('.', $path),
                'flat' => join('_', $path),
            ];
        }

        return $results;
    }
}
