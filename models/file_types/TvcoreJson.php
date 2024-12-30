<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class TvcoreJson
{
    public static function getDataFromRemoteJson(string $link)
    {
        $curl = curl_init($link);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
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

    public static function getFile($file_path)
    {
        if (is_file($file_path)) {
            $json_string = Tools::file_get_contents($file_path);

            return json_decode($json_string, true);
        }

        return false;
    }

    public static function setFile($cache_file, $contents, bool|string $column = false)
    {
        $result = [];
        if (is_string($column)) {
            foreach ($contents as $row) {
                $result[][$column] = $row;
            }
        } else {
            $result = $contents;
        }

        $jsonString = json_encode($result, JSON_UNESCAPED_UNICODE);

        // Write in the file
        $fp = fopen($cache_file, 'w');
        fwrite($fp, $jsonString);
        fclose($fp);
        @chmod($cache_file, 0644);
    }

    /**
     * @param array $params
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getAdminSideNode(array $params)
    {
        require_once _PS_MODULE_DIR_ . 'tvimport/models/TvimportFile.php';

        $result = [];

        $id_tv_import_file = $params['id_tv_import_file'];
        $file_type = $params['file_type'];

        if (Validate::isUnsignedInt($id_tv_import_file)) {
            $file = new TvimportFile($id_tv_import_file);
            if ($file_type == 0) {
                // CSV
                require_once _PS_MODULE_DIR_ . 'tvcore/models/file_types/TvcoreCsv.php';

                $row = TvcoreCsv::getRowData($file_link, $node_index, $exclude_row, $delimiter);

                $i = 0;
                $node = '';
                $available_fields = json_decode($file->available_fields, true);
                ksort($available_fields);
                foreach ($row as $key => $value) {
                    $node .= '<div class="element lvl_0" data-path="//col' . $key . '"><div class="expander">-</div>' .
                        '<div class="tag">' .
                        htmlentities('<') .
                        '<span class="tag_name">' . $available_fields[$key] . '</span>' .
                        htmlentities('>') .
                        '</div><div class="content">' . trim($row[$i], '"') . '</div><div class="tag">' .
                        htmlentities('</') . '<span class="tag_name">' . $available_fields[$key] . '</span>' .
                        htmlentities('>') . '</div></div>';
                    ++$i;
                }
                $result['node'] = $node;
                if ($api == 1) {
                    // Icecat
                    $result['id_api'] = pSQL(trim($row[$api_column], '"'));
                }
            } elseif ($file_type == 1) {
                // XML
                require_once _PS_MODULE_DIR_ . 'tvcore/models/file_types/TvcoreXml.php';

                $result['node'] = TvcoreXml::getPrettyPrintedNode(
                    $params['file_link'],
                    $params['node_index'],
                    $params['tag']
                );
            }
        } else {
            exit(json_encode(['error_code' => 1], JSON_UNESCAPED_UNICODE));
        }

        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    //2025
    public static function getDataFromLocalFile(string $path)
    {
        return json_decode(file_get_contents($path), true);
    }
}
