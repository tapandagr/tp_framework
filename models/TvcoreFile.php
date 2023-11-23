<?php

class TvcoreFile
{
    public static function getJson($cached_file)
    {
        $json_string = file_get_contents($cached_file);
        return json_decode($json_string);
    }

    public static function setJson($cache_file, $contents)
    {
        $jsonString = json_encode($contents, JSON_UNESCAPED_UNICODE);
        // Write in the file
        $fp = fopen($cache_file, 'w');
        fwrite($fp, $jsonString);
        fclose($fp);
        @chmod($cache_file, 0664);
    }

    public static function arrayToXml($array, &$xml)
    {
        foreach ($array as $key => $value) {
            // We make sure there is no index starting with a number :)
            $tmp_key = '__' . $key;
            if (is_array($value) || is_object($value)) {
                $subnode = $xml->addChild($tmp_key);
                self::arrayToXml($value, $subnode);
            } else {
                $xml->addChild($tmp_key, htmlspecialchars($value));
            }
        }
    }

    public static function getXmlFromArray($data)
    {
        $xml = new SimpleXMLElement('<node/>');
        self::arrayToXml($data, $xml);
        return $xml;
        //tvimport::debug($json_string);
        //return json_decode($json_string, JSON_UNESCAPED_UNICODE);
    }

    public static function getJsonFromRemoteFile(string $link)
    {
        $curl = curl_init($link);
        curl_setopt_array($curl, [
            //CURLOPT_NOBODY => true, // use HEAD method, no body data
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_SSL_VERIFYPEER => false,
        ]);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code == '200') {
            // We get data, proceed
            $json_string = file_get_contents($link);
            return json_decode($json_string);
        }

        return false;
    }

    /**
     * 2024
     * @param string $file_link
     * @param bool $exclude_first_row
     * @param string $values_separated
     * @param int $ignore
     * @return array
     */
    public static function getAllCsvRecords(
        string $file_link,
        bool   $exclude_first_row = true,
        string $values_separated = ';',
        int    $ignore = 0
    )
    {
        ini_set('memory_limit', '8192M');
        $file_contents = file_get_contents($file_link);
        $lines = explode(PHP_EOL, $file_contents);
        $result = [];

        $i = 0;
        if ($exclude_first_row) {
            ++$i;
        }

        $i += $ignore;

        $last_index = sizeof($lines) - 1;
        // If last row is empty, get rid of it
        if ($lines[$last_index] == '') {
            unset($lines[$last_index]);
        }

        while (isset($lines[$i])) {
            $values = explode($values_separated, $lines[$i]);
            if (sizeof($values) == 1) {
                if ($values_separated == ';') {
                    $values_separated = ',';
                } else {
                    $values_separated = ';';
                }
                $values = explode($values_separated, $lines[$i]);
            }

            $result[$i] = [];
            foreach ($values as $k => $v) {
                $result[$i]['col' . $k] = trim($v, '"');
            }

            ++$i;
        }

        return $result;
    }

    public static function getCsvSlice(array $csv)
    {
        $ignore = Tools::getValue('ignore');
        $step = Tools::getValue('step');

        return array_slice($csv, $ignore, $step);
    }

    public static function getCsvTotal(string $link, $exclude_first_row)
    {
        ini_set('memory_limit', '8192M');
        //$this->getFileLink();
        $file_contents = file_get_contents($link);
        $records = explode(PHP_EOL, $file_contents);

        // If last rows are empty, get rid of them
        $i = sizeof($records) - 1;
        while ($records[$i] == '') {
            unset($records[$i]);
            --$i;
        }

        $csv_total = sizeof($records);
        // If the file contains headers, reduce by one
        if ($exclude_first_row == 1) {
            --$csv_total;
        }

        return $csv_total;
    }
}
