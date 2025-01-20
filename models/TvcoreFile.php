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
class TvcoreFile
{
    protected static array $mime_types = [
        'application/json' => 'json', // json
        'application/octet-stream' => 'xlsx', // xlsx (?)
        // "application/pdf", // pdf
        // "application/msword", // MS Word
        'application/vnd.ms-excel' => 'xls', // MS Excel
        'application/vnd.ms-office' => 'xls', // xls
        // "application/vnd.ms-powerpoint", // MS Powerpoint
        // "application/vnd.oasis.opendocument.presentation", // odp
        'application/vnd.oasis.opendocument.spreadsheet' => 'ods', // ods
        // "application/vnd.oasis.opendocument.text", // odt
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx', // xlsx
        // "application/vnd.openxmlformats-officedocument.wordprocessingml.document", // docx
        'application/xml' => 'xml', // xml
        'application/zip' => 'zip', // zip
        'text/csv' => 'csv', // csv
        'text/plain' => 'txt', // csv, json (?)
        'text/xml' => 'xml',
        'text/x-php' => 'php',
        // To be reviewed
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
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

    public static function getXmlFromArray($data): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<node/>');
        self::arrayToXml($data, $xml);

        return $xml;
    }

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
    public static function getCsvSlice(
        string $csv_path,
        string $values_separator,
        int $slice_size = 5,
        int $ignore = 1
    ) {
        $records = self::getAllCsvRecords($csv_path, $values_separator, $ignore);

        return array_slice($records, 0, $slice_size);
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

    /**
     * @param string $dir_path
     * @param array $dir_names
     * @param string $module_name
     * @return string
     */
    public static function mkdirBulk(string $dir_path, array $dir_names, string $module_name): string
    {
        foreach ($dir_names as $dir_name) {
            $dir_path .= '/' . $dir_name;
            TvcoreFile::mkdir($dir_path, $module_name);
        }

        return $dir_path;
    }

    /**
     * @param string $dir_path
     * @param string $module_name
     * @return void
     */
    public static function mkdir(string $dir_path, string $module_name)
    {
        echo $dir_path;
        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0755, true);
            self::copy(_PS_MODULE_DIR_ . $module_name, $dir_path, 'index.php');
        }
        @chmod($dir_path, 0755);
    }

    public static function copy(string $origin, string $destination, string $file_name)
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
     * @param int $file_type
     * @return false|string
     */
    public static function copyRemoteFileToServer($origin, $destination, $file_name, int $file_type = 0)
    {
        $extension = self::$reverse_file_types[$file_type];
        if ($extension) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $origin);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            $destination .= '/' . $file_name . '.' . $extension;
            $file = fopen($destination, 'w+');
            fputs($file, $data);
            fclose($file);
            chmod($destination, 0644);

            return $destination;
        }

        return false;
    }

    public static function pullRemoteFile(
        string $link,
        string $tmp_destination,
        string $final_destination,
        string $file_name
    ): false|array {
        $origin = $link;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $origin);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        $tmp_destination .= '/' . time();
        $file = fopen($tmp_destination, 'w+');
        fputs($file, $data);
        fclose($file);
        chmod($tmp_destination, 0644);
        $mime = self::getMimeType($tmp_destination);
        if ($mime) {
            $extension = self::$mime_types[$mime];
            $final_destination .= '/' . $file_name . '.' . $extension;
            rename($tmp_destination, $final_destination);

            return [
                'path' => $final_destination,
                'extension' => $extension,
            ];
        }

        unlink($tmp_destination);

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

    public static function unzip($file, $destination)
    {
        $zip = new ZipArchive();
        $result = $zip->open($file);
        if ($result === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
    }

    public static function relocate($source, $target): void
    {
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    self::relocate($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
            self::deleteDirectory($source);
        } else {
            copy($source, $target);
        }
    }

    public static function deleteDirectory(string $directory_path): bool
    {
        if (is_dir($directory_path)) {
            self::deleteDirectoryContents($directory_path);
            rmdir($directory_path);
        }

        return true;
    }

    public static function deleteDirectoryContents(
        string $parent_directory,
        array $relative_whitelisted_dirs = [],
        array $whitelisted_files = [],
    ) {
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
            $file_name = $fileinfo->getFilename();
            $file_path = $fileinfo->getRealPath();
            if ($fileinfo->isDir()) {
                if (in_array($file_path, $whitelisted_dirs)) {
                    continue; // The children have already been deleted
                } else {
                    rmdir($file_path);
                }
            } elseif (!in_array($file_name, $whitelisted_files)
                && !str_contains($file_path, '~lock')
                && in_array($file_path, $whitelisted_dirs)
            ) {
                continue; // We do need files such as index.php or .htaccess
            } else {
                unlink($file_path); // Blacklisted file
            }
        }
    }

    public static function getTemplateParentDir($module_name): array
    {
        return [
            'module_admin' => _PS_MODULE_DIR_ . $module_name . '/views/templates/admin',
            'admin' => _PS_ROOT_DIR_ . '/' . _PS_ADMIN_DIR_ONLY_ . '/themes/default/template/controllers',
        ];
    }

    public static function getDirectoryFiles(
        string $dir_path,
        array $include_extensions = [],
        array $exclude_files = ['index.php', '.htaccess', '.DS_Store'],
    ): array {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir_path,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $result = [];

        if (empty($include_extensions)) {
            self::getDirectoryFilesExtensionAll($result, $files, $exclude_files);
        } else {
            self::getDirectoryFilesExtensionSpecific($result, $files, $exclude_files, $include_extensions);
        }

        return $result;
    }

    public static function getDirectoryFilesExtensionAll(
        array &$result,
        RecursiveIteratorIterator $files,
        array $exclude_files): void
    {
        foreach ($files as $fileinfo) {
            $file_path = $fileinfo->getRealPath();
            $file_name_with_extension = $fileinfo->getFilename();
            if (!in_array($file_name_with_extension, $exclude_files) && !str_contains($file_path, '~lock')) {
                $extension = self::getExtension($file_path);
                $file_name_without_extension = $fileinfo->getBasename($extension);
                $result[$file_name_without_extension] = $file_path;
            }
        }
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

    public static function getDirectoryFilesExtensionSpecific(
        array &$result,
        RecursiveIteratorIterator $files,
        array $exclude_files,
        array $include_extensions): void
    {
        foreach ($files as $fileinfo) {
            $file_path = $fileinfo->getRealPath();
            $file_name_with_extension = $fileinfo->getFilename();
            if (!in_array($file_name_with_extension, $exclude_files) && !str_contains($file_path, '~lock')) {
                $extension = self::getExtension($file_path);
                $file_name_without_extension = $fileinfo->getBasename('.' . $extension);
                if (in_array($extension, $include_extensions)) {
                    $result[$file_name_without_extension] = $file_path;
                }
            }
        }
    }

    public static function installPrestaShopValidation(string $module_dir): void
    {
        $php_files = self::getDirectoryFiles(_PS_MODULE_DIR_ . $module_dir, ['php']);
        foreach ($php_files as $php_file) {
            $result = [];
            $lines = @file_get_contents($php_file);
            $explode = explode(PHP_EOL, $lines);

            $line_key = 0;
            while (isset($explode[$line_key]) && !str_contains($explode[$line_key], 'PrestaShop validator - Start')) {
                $result[] = $explode[$line_key];
                ++$line_key;
            }

            if (isset($explode[$line_key])) {
                $result[] = $explode[$line_key];
                ++$line_key;

                $result = array_merge(
                    $result,
                    [
                        "if (!defined('_PS_VERSION_')) {",
                        "\texit;",
                        '}',
                    ]
                );

                while (isset($explode[$line_key]) && !str_contains($explode[$line_key], 'PrestaShop validator - Finish')) {
                    ++$line_key;
                }

                if (isset($explode[$line_key])) {
                    $result[] = $explode[$line_key];
                    ++$line_key;
                }

                $result = array_merge($result, array_slice($explode, $line_key));
                file_put_contents($php_file, implode(PHP_EOL, $result));
            }
        }
    }

    public static function uninstallPrestaShopValidation(string $module_dir): void
    {
        $php_files = self::getDirectoryFiles(_PS_MODULE_DIR_ . $module_dir, ['php']);
        foreach ($php_files as $php_file) {
            $result = [];
            $lines = @file_get_contents($php_file);
            $explode = explode(PHP_EOL, $lines);
            $line_key = 0;
            while (isset($explode[$line_key]) && !str_contains($explode[$line_key], 'PrestaShop validator - Start')) {
                $result[] = $explode[$line_key];
                ++$line_key;
            }

            if (isset($explode[$line_key])) {
                $result[] = $explode[$line_key];
                ++$line_key;

                while (isset($explode[$line_key]) && !str_contains($explode[$line_key], 'PrestaShop validator - Finish')) {
                    ++$line_key;
                }

                $result = array_merge($result, array_slice($explode, $line_key));
                file_put_contents($php_file, implode(PHP_EOL, $result));
            }
        }
    }

    public static function updateModuleSettings(Module $module): void
    {
        $values = [];
        foreach ($module->getSettings(false) as $setting_key => $setting) {
            if (isset($setting['language']) && $setting['language'] === true) {
                foreach (Language::getLanguages(false, false, true) as $id_lang) {
                    $key = $setting_key . '_' . $id_lang;
                    if (null !== Tools::getValue($key)) {
                        $value = Tools::getValue($key);
                        if (Validate::{$setting['validation']}($value)) {
                            $values[$setting_key][$id_lang] = $value;
                        }
                    }
                }
            } elseif (null !== Tools::getValue($setting_key)) {
                $value = Tools::getValue($setting_key);
                if (Validate::{$setting['validation']}($value)) {
                    $values[$setting_key] = $value;
                }
            }
        }

        foreach ($values as $key => $key) {
            Configuration::updateValue($key, $values[$key]);
        }
    }
}
