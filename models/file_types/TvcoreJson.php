<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2024 © tivuno.com
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

            return json_decode($json_string);
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

        //print $cache_file;

        $jsonString = json_encode($result, JSON_UNESCAPED_UNICODE);

        // Write in the file
        $fp = fopen($cache_file, 'w');
        fwrite($fp, $jsonString);
        fclose($fp);
        @chmod($cache_file, 0664);
    }

    /**
     * @param int $id_tv_import_file
     * @param string $file_link
     * @param int $node_index
     * @param int $file_type
     * @param int $exclude_row
     * @param string $delimiter
     * @param int $api
     * @param int $api_column
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getAdminSideNode(
        int    $id_tv_import_file,
        string $file_link,
        int    $node_index,
        int    $file_type = 1,
        int    $exclude_row = 1,
        string $delimiter = ';',
        int    $api = 0,
        int    $api_column = 2,
    )
    {
        require_once _PS_MODULE_DIR_ . 'tvimport/models/TvimportFile.php';

        $result = [];

        if (Validate::isUnsignedInt($id_tv_import_file)) {
            $file = new TvimportFile($id_tv_import_file);
            if ($file_type == 0) {
                // CSV
                require_once _PS_MODULE_DIR_ . 'tvcore/models/file_types/TvcoreCsv.php';

                $row = TvcoreCsv::getRowData($file_link, $node_index, $exclude_row, $delimiter);
                //$fields = json_decode($file->available_fields);

                //$file_contents = file_contents($file->file_link);

                //$id_index = $file->settings->creation->api->identifier;
                //$result['id'] = trim($row[ltrim($id_index, '//__col__')], '"');

                $i = 0;
                $node = '';
                $available_fields = json_decode($file->available_fields, true);
                ksort($available_fields);
                foreach ($row as $key => $value) {
                    $node .= '<div ' . 'class="element lvl_0 ' . '" ' . 'data-path="//col' . $key . '">'
                        . '<div class="expander">-</div>';

                    $node .= '<div class="tag">' . htmlentities('<') . '<span class="tag_name">' . $available_fields[$key] . '</span>' . htmlentities('>') . '</div>' . '<div class="content">' . trim($row[$i],
                            '"') . '</div>' . '<div class="tag">' . htmlentities('</') . '<span class="tag_name">' . $available_fields[$key] . '</span>' . htmlentities('>') . '</div>';
                    $node .= '</div>';
                    ++$i;
                }
                $result['node'] = $node;
                if ($api == 1) {
                    //tvimport::debug($row);
                    // Icecat
                    $result['id_api'] = pSQL(trim($row[$api_column], '"'));
                }

            } elseif ($file_type == 1) {
                // XML
                $record_node = $file::getRecordNode($file->settings->default->data_path);
                $xml = new XMLReader();
                $xml->open($link);
                //echo $record_node;
                /*while ($xml->read()) {
                    echo $xml->name . "<br />";
                }*/
                while ($xml->read() && $xml->name != $record_node) {
                    ;
                }
                while ($xml->name == $record_node) {
                    $element = new SimpleXMLElement($xml->readOuterXML());
                    //echo $file->filter . "<br />";
                    $v = $element->xpath($file->filter); // | //product_id[. ="21"] | //name[starts-with(.,"BULLE")]');
                    if ($v) {
                        //echo "<pre>";
                        //echo " RecursiveIteratorIterator \n";

                        $sxi = new RecursiveIteratorIterator(
                            new SimpleXMLIterator($element->asXML()),
                            RecursiveIteratorIterator::SELF_FIRST
                        );

                        $node = '<div class="element lvl_0">' . '<div class="expander">-</div>';
                        $node .= '<div class="tag">' . htmlentities('<') . '<span class="tag_name">' . $record_node . '</span>' . htmlentities('>') . '</div><div class="content">';
                        //$index = 1;
                        $relative_path = '';
                        foreach ($sxi as $key => $value) {
                            $lvl = (int) $sxi->getDepth() + 1;

                            $relative_path = '';
                            $field_items = $element->xpath('//' . $key);
                            foreach ($field_items as $field_item) {
                                $parent = $field_item->xpath('ancestor-or-self::*');
                                foreach ($parent as $p) {
                                    if ($p->getName() == $record_node) {
                                        continue;
                                    }
                                    $relative_path .= '/' . $p->getName();
                                }
                                //echo PHP_EOL;
                            }

                            $relative_path = '[' . $relative_path . ']';

                            //echo $key;

                            //echo $index . ' --- ' . $sxi->getName() . "<br />";

                            $classes = [];
                            $expander = '';

                            if (!$sxi->hasChildren()) {
                                $classes[] = 'string';

                                if (strlen($value) <= 40) {
                                    $classes[] = 'short';
                                } else {
                                    $expander .= '<div class="expander">-</div>';
                                }
                            }

                            //echo
                            $node .= '<div ' . 'class="element lvl_' . $lvl . ' ' . implode(
                                    ' ',
                                    $classes
                                ) . '" ' . 'data-path="' . $relative_path . '">' . $expander;

                            $node .= '<div class="tag">' . htmlentities('<') . '<span class="tag_name">' . $key . '</span>' . htmlentities('>') . '</div>' . '<div class="content">' . $value . '</div>' . '<div class="tag">' . htmlentities('</') . '<span class="tag_name">' . $key . '</span>' . htmlentities('>') . '</div>';

                            $node .= '</div>'; // !element closing tag
                            // $currentDepth = $sxi->getDepth();
                            //$index++;
                        }
                        $node .= '</div>' // !content closing tag
                            . '<div class="tag">' . htmlentities('</') . '<span class="tag_name">' . $record_node . '</span>' . htmlentities('>') . '</div>';
                        $node .= '</div>';
                        $xml->next($record_node);
                        unset($element);
                        $result['node'] = $node;
                        break;
                    } else {
                        //echo $xml->name . "<br />";
                    }
                    $xml->next($record_node);

                    //break;
                }
            }
        } else {
            exit(json_encode(['error_code' => 1], JSON_UNESCAPED_UNICODE));
        }
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
