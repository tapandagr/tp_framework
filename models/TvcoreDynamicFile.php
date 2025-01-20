<?php
class TvcoreDynamicFile
{
    public static function getMimeTypeFunction(&$content, $spaces = 1): void
    {
        $space = str_repeat('    ', $spaces);
        $content .= PHP_EOL .
            $space . 'public static function getMimeType(string $file_path): string' . PHP_EOL .
            $space . '{' . PHP_EOL .
            $space . '    $finfo = finfo_open(FILEINFO_MIME_TYPE);' . PHP_EOL .
            $space . '    $mime_type = finfo_file($finfo, $file_path);' . PHP_EOL .
            $space . '    if (isset(self::$mime_types[$mime_type])) {' . PHP_EOL .
            $space . '        return $mime_type;' . PHP_EOL .
            $space . '    }' . PHP_EOL . PHP_EOL .
            $space . '    return false;' . PHP_EOL .
            $space . '}' . PHP_EOL;
    }

    public static function pullRemoteFileFunction(&$content, int $spaces = 1, int $parent_directories = 0): void
    {
        $space = str_repeat('    ', $spaces);
        $space2 = str_repeat('    ', $spaces + 1);
        $content .= PHP_EOL .
            $space . 'public static function pullRemoteFile(string $origin, string $destination, string $file_name): false|array' . PHP_EOL .
            $space . '{' . PHP_EOL .
            $space2 . '$ch = curl_init();' . PHP_EOL .
            $space2 . 'curl_setopt($ch, CURLOPT_URL, $origin);' . PHP_EOL .
            $space2 . 'curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);' . PHP_EOL .
            $space2 . '$data = curl_exec($ch);' . PHP_EOL .
            $space2 . 'curl_close($ch);' . PHP_EOL .
            $space2 . '$tmp_destination = __DIR__ . \'/../../uploads/tmp/\' . time();' . PHP_EOL .
            $space2 . '$file = fopen($tmp_destination, \'w+\');' . PHP_EOL .
            $space2 . 'fputs($file, $data);' . PHP_EOL .
            $space2 . 'fclose($file);' . PHP_EOL .
            $space2 . '@chmod($tmp_destination, 0644);' . PHP_EOL .
            $space2 . '$mime = self::getMimeType($tmp_destination);' . PHP_EOL .
            $space2 . 'if ($mime) {' . PHP_EOL .
            $space2 . '    $extension = self::$mime_types[$mime];' . PHP_EOL .
            $space2 . '    $destination .= \'/\' . $file_name . \'.\' . $extension;' . PHP_EOL .
            $space2 . '    rename($tmp_destination, $destination);' . PHP_EOL .
            $space2 . '    @chmod($destination, 0644);' . PHP_EOL .
            $space2 . '    return [' . PHP_EOL .
            $space2 . '        \'path\' => $destination,' . PHP_EOL .
            $space2 . '        \'extension\' => $extension,' . PHP_EOL .
            $space2 . '    ];' . PHP_EOL .
            $space2 . '}' . PHP_EOL .
            $space2 . 'unlink($tmp_destination);' . PHP_EOL . PHP_EOL .
            $space2 . 'return false;' . PHP_EOL .
            $space . '}' . PHP_EOL;
    }

    public static function downloadZipFunction(string &$contents, int $spaces = 1): void
    {
        $space = str_repeat('    ', $spaces);
        $space1 = str_repeat('    ', $spaces + 1);
        $space2 = str_repeat('    ', $spaces + 2);
        $contents .= PHP_EOL .
            $space . 'public static function downloadZip(string $file_path): void' . PHP_EOL .
            $space . '{' . PHP_EOL .
            $space1 . 'if (file_exists($file_path)) {' . PHP_EOL .
            $space2 . "header('Content-Description: File Transfer');" . PHP_EOL .
            $space2 . "header('Content-Type: application/zip');" . PHP_EOL .
            $space2 . "header('Content-Disposition: attachment; filename=' . basename(\$file_path));" . PHP_EOL .
            $space2 . "header('Expires: 0');" . PHP_EOL .
            $space2 . "header('Cache-Control: must-revalidate');" . PHP_EOL .
            $space2 . "header('Pragma: public');" . PHP_EOL .
            $space2 . "header('Content-Length: ' . filesize(\$file_path));" . PHP_EOL .
            $space2 . "ob_clean();" . PHP_EOL .
            $space2 . "flush();" . PHP_EOL .
            $space2 . "readfile(\$file_path);" . PHP_EOL .
            $space2 . "exit;" . PHP_EOL .
            $space1 . '}' . PHP_EOL .
            $space . '}' . PHP_EOL;
    }

    public static function zipFileFunction(string &$contents, array $config): void
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function zipFile(string $destination, string $file_path): void' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . '$zip = new ZipArchive;' . PHP_EOL .
            $config['space2'] . 'if ($zip->open($destination, ZipArchive::CREATE) === TRUE) {' . PHP_EOL .
            $config['space3'] . '$download_file = file_get_contents($file_path);' . PHP_EOL .
            $config['space3'] . '$zip->addFromString(basename($file_path), $download_file);' . PHP_EOL .
            $config['space3'] . '$zip->close();' . PHP_EOL .
            $config['space2'] . '}' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getIsJsonFunction(string &$contents, $config): void
    {
        $contents .= PHP_EOL .
            $config['space1'] . '// Validation method from PrestaShop' . PHP_EOL .
            $config['space1'] . 'public static function isJson(string $string): false|int' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . 'json_decode($string);' . PHP_EOL . PHP_EOL .
            $config['space2'] . 'return json_last_error() === JSON_ERROR_NONE;' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function isLinkRewriteFunction(string &$contents, $config): void
    {
        $contents .= PHP_EOL .
            $config['space1'] . '// Validation method from PrestaShop' . PHP_EOL .
            $config['space1'] . 'public static function isLinkRewrite(string $string): false|int' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . "return preg_match('/^[_a-zA-Z0-9\-]+$/', \$string);" . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getPsofaFunction(string &$contents, $config): void
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function psofa(): void' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . "echo 'Θέλετε αλάτι στο αυγό σας;';" . PHP_EOL .
            $config['space2'] . 'http_response_code(403);' . PHP_EOL .
            $config['space2'] . 'exit;' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getMkdirBulkFunction(string &$contents, $config)
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function mkdirBulk(string $parent_dir, array $sub_dirs): string' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . 'foreach ($sub_dirs as $sub_dir) {' . PHP_EOL .
            $config['space3'] . '$parent_dir .= \'/\' . $sub_dir;' . PHP_EOL .
            $config['space3'] . 'if (!is_dir($parent_dir)) {' . PHP_EOL .
            $config['space4'] . 'self::mkdir($parent_dir, \'tvimport\');' . PHP_EOL .
            // $config['space4'] . '@chmod($parent_dir, 0755);' . PHP_EOL .
            $config['space3'] . '}' . PHP_EOL .
            $config['space2'] . '}' . PHP_EOL . PHP_EOL .
            $config['space2'] . 'return $parent_dir;' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getMkdirFunction(string &$contents, $config)
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function mkdir(string $dir_path, string $module_name): void' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . 'if (!is_dir($dir_path)) {' . PHP_EOL .
            $config['space3'] . 'mkdir($dir_path, 0755, true);' . PHP_EOL .
            $config['space3'] . "self::copy(__DIR__ . '/../../../' . \$module_name, \$dir_path, 'index.php');" . PHP_EOL .
            $config['space2'] . '}' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getCopyFunction(string &$contents, $config)
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function copy(string $origin, string $destination, string $file_name): bool' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . '$origin = $origin . \'/\' . $file_name;' . PHP_EOL .
            $config['space2'] . '$destination = $destination . \'/\' . $file_name;' . PHP_EOL .
            $config['space2'] . 'copy($origin, $destination);' . PHP_EOL .
            $config['space2'] . 'chmod($destination, 0644);' . PHP_EOL . PHP_EOL .
            $config['space2'] . 'return true;' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getConvertToJsonFunction(string &$contents, $config): void
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function convertToJson(string $xml_path, string $json_path): string' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . "\$xml = (array) simplexml_load_file(\$xml_path, 'SimpleXMLElement', LIBXML_NOCDATA);" . PHP_EOL .
            $config['space2'] . '$json_string = str_replace(' . PHP_EOL .
            $config['space3'] . '[' . PHP_EOL .
            $config['space4'] . '\'\n            \',' . PHP_EOL .
            $config['space3'] . '],' . PHP_EOL .
            $config['space3'] . '[' . PHP_EOL .
            $config['space4'] . "''," . PHP_EOL .
            $config['space3'] . '],' . PHP_EOL .
            $config['space3'] . "json_encode(\$xml, JSON_UNESCAPED_UNICODE)" . PHP_EOL .
            $config['space2'] . ');' . PHP_EOL . PHP_EOL .
            $config['space2'] . 'file_put_contents($json_path, $json_string);' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }

    public static function getSetCamelCaseFunction(string &$contents, $config): void
    {
        $contents .= PHP_EOL .
            $config['space1'] . 'public static function setCamelCase(string $string): string' . PHP_EOL .
            $config['space1'] . '{' . PHP_EOL .
            $config['space2'] . '$result = \'\';' . PHP_EOL .
            $config['space2'] . '$explode = explode(\'-\', $string);' . PHP_EOL .
            $config['space2'] . 'foreach ($explode as $value) {' . PHP_EOL .
            $config['space3'] . '$result .= ucfirst($value);' . PHP_EOL .
            $config['space2'] . '}' . PHP_EOL . PHP_EOL .
            $config['space2'] . 'return $result;' . PHP_EOL .
            $config['space1'] . '}' . PHP_EOL;
    }
}
