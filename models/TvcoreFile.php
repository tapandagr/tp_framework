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

class TvcoreFile
{
    public static function getJson($file_path)
    {
        if (is_file($file_path)) {
            $json_string = Tools::file_get_contents($file_path);

            return json_decode($json_string, true);
        }

        return false;
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
    }

    public static function getJsonFromRemoteFile(string $link)
    {
        $curl = curl_init($link);
        curl_setopt_array($curl, [
            // CURLOPT_NOBODY => true, // use HEAD method, no body data
            CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_SSL_VERIFYPEER => false,
        ]);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code == '200') {
            // We get data, proceed
            $json_string = Tools::file_get_contents($link);

            return json_decode($json_string);
        }

        return false;
    }

    public static function pretty($json)
    {
        echo '<pre>';
        print_r($json);
        echo '</pre>';
    }

    /**
     * 2024
     *
     * @param string $file_link
     * @param string $values_separated
     * @param int $ignore
     *
     * @return array
     */
    public static function getAllCsvRecords(
        string $file_link,
        string $values_separated = ';',
        int $ignore = 0
    ) {
        ini_set('memory_limit', '8192M');
        $file_contents = Tools::file_get_contents($file_link);
        $lines = explode(PHP_EOL, $file_contents);
        $result = [];

        $i = $ignore;

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

    public static function getCsvRecords(
        string $file_link,
        bool $exclude_first_row = true,
        string $values_separated = ';',
        int $ignore = 0,
        int $step = 0
    ) {
        ini_set('memory_limit', '8192M');
        $file_contents = Tools::file_get_contents($file_link);
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

    /**
     * It returns a slice from a given csv file
     * @param string $csv_path
     * @param string $values_separator
     * @param int $slice_size
     * @param int $ignore
     * @return array
     */
    public static function getCsvSlice(string $csv_path, string $values_separator, int $slice_size = 5, int $ignore = 1)
    {
        $records = self::getAllCsvRecords($csv_path, $values_separator, $ignore);

        return array_slice($records, 0, $slice_size);
    }

    public static function getCsvTotal(string $link, $exclude_first_row)
    {
        ini_set('memory_limit', '8192M');
        // $this->getFileLink();
        $file_contents = Tools::file_get_contents($link);
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

    public static function getDirFiles(string $dir_path)
    {
        $result = [];
        $files = scandir($dir_path);
        $files = array_diff($files, ['.', '..', 'index.php']);
        foreach ($files as $file) {
            $file_path = $dir_path . '/' . $file;
            if (is_file($file_path)) {
                $path_parts = pathinfo($file_path);
                $result[] = $path_parts['filename'];
            }
        }

        return $result;
    }

    public static function deleteDirectory(string $dir)
    {
        self::deleteDirectoryContents($dir);
        // rmdir($dir);
    }

    public static function getFileType(string $file_path)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        return finfo_file($finfo, $file_path);
    }

    public static function deleteDirectoryContents(string $dir, array $relative_whitelisted_dirs = [])
    {
        $whitelisted_dirs = [$dir];

        foreach ($relative_whitelisted_dirs as $whitelisted_dir) {
            $whitelisted_dirs[] = $dir . $whitelisted_dir;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir() && in_array($fileinfo->getRealPath(), $whitelisted_dirs)) {
                continue; // The children have already been deleted
            } elseif ($fileinfo->getFilename() == 'index.php'
                && in_array($fileinfo->getPath(), $whitelisted_dirs)
            ) {
                continue; // We do need the index.php file from whitelisted directory
            } else {
                // Neither whitelisted directory, nor index.php of whitelisted => Delete everything
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
        }
    }

    public static function getSubDirectories(string $dir)
    {
        $result = [];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                $dir_path = $fileinfo->getRealPath();
                if (str_contains($dir_path, '.git')) {
                    continue;
                }

                $result[] = $dir_path;
            }
        }

        return $result;
    }

    public static function copyFile(string $origin, string $destination, string $file_name)
    {
        $origin = $origin . '/' . $file_name;
        $destination = $destination . '/' . $file_name;
        copy($origin, $destination);
        chmod($destination, 0644);

        return true;
    }
}
