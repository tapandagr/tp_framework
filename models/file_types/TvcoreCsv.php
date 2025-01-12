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
class TvcoreCsv
{
    public static function getRowData(
        $file_link,
        int $node_index,
        int $exclude_rows = 1,
        string $delimiter = ';'
    ) {
        $file_contents = file_get_contents($file_link);
        $lines = explode(PHP_EOL, $file_contents);
        $node_index += $exclude_rows;

        if (isset($lines[$node_index])) {
            $row = explode($delimiter, $lines[$node_index]);
            if (sizeof($row) == 1) {
                if ($delimiter == ';') {
                    $delimiter = ',';
                } else {
                    $delimiter = ';';
                }
            }

            return explode($delimiter, $lines[$node_index]);
        }

        return false;
    }

    public static function getRowsCount(string $file_path, int $exclude_rows = 0): int
    {
        $file_contents = file_get_contents($file_path);
        $lines = explode(PHP_EOL, $file_contents);
        $last_index = sizeof($lines) - 1;
        if ($lines[$last_index] == '') {
            unset($lines[$last_index]);
        }

        return sizeof($lines) - $exclude_rows;
    }

    public static function createFile(string $destination, array $csv_data): void
    {
        $fp = fopen($destination, 'w');
        foreach ($csv_data as $line) {
            fputcsv($fp, $line, ',');
        }
        fclose($fp);
        @chmod($destination, 0644);
    }
}
