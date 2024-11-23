<?php
/**
 * Cornelius - Core PrestaShop module
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
 * @license   https://tivuno.com/blog/bp/business-news/2-basic-license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'tvcore/models/file_types/TvcoreJson.php';

class TvcoreFile
{
    protected static array $mime_types = [
        'application/json' => 'json', // json
        'application/octet-stream' => 'xlsx', // xlsx (?)
        //"application/pdf", // pdf
        //"application/msword", // MS Word
        'application/vnd.ms-excel' => 'xls', // MS Excel
        'application/vnd.ms-office' => 'xls', // xls
        //"application/vnd.ms-powerpoint", // MS Powerpoint
        //"application/vnd.oasis.opendocument.presentation", // odp
        'application/vnd.oasis.opendocument.spreadsheet' => 'ods', // ods
        //"application/vnd.oasis.opendocument.text", // odt
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx', // xlsx
        //"application/vnd.openxmlformats-officedocument.wordprocessingml.document", // docx
        'application/xml' => 'xml', // xml
        'application/zip' => 'zip', // zip
        'text/csv' => 'csv', // csv
        'text/plain' => 'txt', // csv, json (?)
        'text/xml' => 'xml',
    ];

    protected static array $file_types = [
        'csv' => 0,
        'xml' => 1,
        'xls' => 2,
        'xlsx' => 3,
        'json' => 5,
    ];

    protected static array $reverse_file_types = [
        0 => 'csv',
        1 => 'xml',
        2 => 'xls',
        3 => 'xlsx',
        4 => 'json',
    ];

    public static function arrayToXml($array, &$xml): void
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

    public static function getXmlFromArray($data): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<node/>');
        self::arrayToXml($data, $xml);

        return $xml;
    }

    // deprecated
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

            return json_decode($json_string, true);
        }

        return false;
    }

    public static function pretty($json, bool $stop = true): void
    {
        echo '<pre>';
        print_r($json);
        echo '</pre>';

        if ($stop) {
            // We return some random sh!t that does not exist, aiming to break the process
            exit(rand());
        }
    }

    /**
     * 2024
     * @param string $file_link
     * @param string $values_separated
     * @param int $ignore
     * @return array
     */
    public static function getAllCsvRecords(
        string $file_link,
        string $values_separated = ';',
        int    $ignore = 0
    ): array
    {
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
        bool   $exclude_first_row = true,
        string $values_separated = ';',
        int    $ignore = 0,
        int    $step = 0
    ): array
    {
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
    public static function getCsvSlice(
        string $csv_path,
        string $values_separator,
        int    $slice_size = 5,
        int    $ignore = 1
    ): array
    {
        $records = self::getAllCsvRecords($csv_path, $values_separator, $ignore);

        return array_slice($records, 0, $slice_size);
    }

    public static function getCsvTotal(string $link, $exclude_first_row): int
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

    /**
     * @param string $link
     * @return bool
     */
    public static function doesExist(string $link)
    {
        $file_headers = @get_headers($link);

        if ($file_headers and $file_headers[0] == 'HTTP/1.1 200 OK') {
            return true;
        }

        return false;
    }

    public static function getDirectoryFiles(
        string      $dir_path,
        string|bool $json_cache = false
    )
    {
        $files = TvcoreJson::getFile($json_cache);
        if ($files && !_PS_MODE_DEV_) {
            return $files;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $result = [];

        foreach ($files as $fileinfo) {
            if (!$fileinfo->isDir()) {
                // We need only the files
                $file_path = $fileinfo->getRealPath();
                $file_name = $fileinfo->getFilename();
                if (!in_array($file_name, ['index.php', '.htaccess', '.DS_Store'])) {
                    if (!str_contains($file_path, '~lock')) {
                        $extension = self::getExtension($file_path);
                        if (in_array($extension, ['json', 'ods', 'txt', 'xls', 'xlsx', 'xml'])) {
                            $result[]['path'] = $file_path;
                        }
                    }
                }
            }
        }

        if (is_string($json_cache)) {
            TvcoreJson::setFile($json_cache, $result, 'path');
        }

        return $result;
    }

    public static function getExtension(string $file_path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);

        return self::getExtensionByMimeType($mime_type);
    }

    public static function getExtensionByMimeType(string $mime_type)
    {
        if (isset(self::$mime_types[$mime_type])) {
            return self::$mime_types[$mime_type];
        }

        return false;
    }

    public static function getMimeType(string $file_path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);

        if (isset(self::$mime_types[$mime_type])) {
            return $mime_type;
        }

        return false;
    }

    public static function getDirFiles(string $dir_path, array $exclude = [])
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $result = [];

        foreach ($files as $fileinfo) {
            if (!in_array($fileinfo->getFilename(), $exclude)) {
                $name = $fileinfo->getBasename('.' . $fileinfo->getExtension());
                $result[] = [
                    'name' => $name,
                    'path' => $fileinfo->getRealPath(),
                ];
            }
            /*if ($fileinfo->isDir() && in_array($fileinfo->getRealPath(), $whitelisted_dirs)) {
                continue; // The children have already been deleted
            } elseif ($fileinfo->getFilename() == 'index.php'
                && in_array($fileinfo->getPath(), $whitelisted_dirs)
            ) {
                continue; // We do need the index.php file from whitelisted directory
            } else {
                // Neither whitelisted directory, nor index.php of whitelisted => Delete everything
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }*/
        }

        //exit(json_encode($result));
        //$aek();

        return $result;
    }

    public static function deleteDirectory(string $dir): void
    {
        self::deleteDirectoryContents($dir);
        // rmdir($dir);
    }

    public static function getFileType(string $link)
    {
        $headers = get_headers($link, 1);
        $mime_type = strtok($headers['Content-Type'], ';');
        $file_type = false;
        if (self::$mime_types[$mime_type]) {
            $extension = self::$mime_types[$mime_type];
            $file_type = self::$file_types[$extension];
        }

        return $file_type;
        //$finfo = finfo_open(FILEINFO_MIME_TYPE);

        //return finfo_file($finfo, $file_path);
    }

    public static function deleteDirectoryContents(
        string $parent_directory,
        array  $relative_whitelisted_dirs = [],
        string $module_name = 'tvcore'
    ): void
    {
        $whitelisted_dirs = [$parent_directory];

        foreach ($relative_whitelisted_dirs as $whitelisted_dir) {
            $whitelisted_dirs[] = $parent_directory . $whitelisted_dir;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $parent_directory,
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

        copy(_PS_MODULE_DIR_ . $module_name . '/index.php', $parent_directory . '/index.php');
    }

    public static function getSubDirectories(string $dir): array
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

    /**
     * @param string $dir_path
     * @param string $module_name
     * @return void
     */
    public static function mkdir(string $dir_path, string $module_name): void
    {
        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0775, true);
            self::copy(_PS_MODULE_DIR_ . $module_name, $dir_path, 'index.php');
        }
    }

    /**
     * @param string $dir_path
     * @param array $dir_names
     * @param string $module_name
     * @return void
     */
    public static function mkdirBulk(string $dir_path, array $dir_names, string $module_name): void
    {
        foreach ($dir_names as $dir_name) {
            $dir_path .= '/' . $dir_name;
            TvcoreFile::mkdir($dir_path, $module_name);
        }
    }

    public static function copy(string $origin, string $destination, string $file_name): true
    {
        $origin = $origin . '/' . $file_name;
        $destination = $destination . '/' . $file_name;
        copy($origin, $destination);
        chmod($destination, 0644);

        return true;
    }

    /**
     * @param $path
     * @param $contents
     * @return true
     */
    public static function addFile($path, $contents): true
    {
        file_put_contents($path, $contents);
        chmod($path, 0644);

        return true;
    }

    /**
     * @param $origin
     * @param $destination
     * @param $file_name
     * @param $file_type
     * @return false|string
     */
    public static function copyRemoteFileToServer($origin, $destination, $file_name, $file_type = 0): false|string
    {
        $extension = self::$reverse_file_types[$file_type];
        if ($extension) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $origin);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            $destination .= '/' . $file_name . '.' . $extension;
            $file = fopen($destination, "w+");
            fputs($file, $data);
            fclose($file);
            chmod($destination, 0644);

            return $destination;
        }

        return false;
    }

    public static function unzip($file, $destination): void
    {
        $zip = new ZipArchive;
        $result = $zip->open($file);
        if ($result === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
        }
    }
}
