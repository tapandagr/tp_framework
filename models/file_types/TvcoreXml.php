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

class TvcoreXml
{
    protected string $prefix = 'col';

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @throws DOMException
     */
    public function convert($data, $prefix = null)
    {
        if ($prefix !== null) {
            $this->setPrefix($prefix);
        }

        $doc = new DOMDocument();
        $doc->formatOutput = true;
        $root = $doc->createElement('root');
        $this->convertInternal($doc, $root, $data);
        $doc->appendChild($root);

        return $doc;
    }

    /**
     * @throws DOMException
     */
    private function convertInternal(DOMDocument $doc, DOMElement &$root, $data)
    {
        foreach ($data as $key => $value) {
            // echo '<br>' . $this->prefix . $key;
            $tmp_key = preg_replace('/[\s\W]+/', '', $key);
            if (is_numeric($key)) {
                $tmp_key = $this->prefix . $key;
            }
            $new_node = $doc->createElement($tmp_key);
            if (is_array($value)) {
                $this->convertInternal($doc, $new_node, $value);
            } else {
                $new_node->nodeValue = htmlspecialchars($value);
            }

            $root->appendChild($new_node);
        }
    }

    /**
     * @param string $file_link
     * @param string $data_path
     * @param int $node_index
     * @return void
     * @throws Exception
     */
    public static function getAdminSideNode(
        string $file_link,
        string $data_path,
        int    $node_index,
    )
    {
        require_once _PS_MODULE_DIR_ . 'tvimport/models/TvimportFile.php';
        $xml = new DOMDocument();
        $result = [];
        exit();
        $xml = new XMLReader();
        $xml->open($file_link);

        $element = new SimpleXMLElement($xml->readString());
        $index = 2;
        $filter = $element->xpath("//table1[$node_index]");
        if ($filter) {
            $sxi = new RecursiveIteratorIterator(
                new SimpleXMLIterator($element->asXML()),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $node = '<div class="element lvl_0">' . '<div class="expander">-</div>';
            $node .= '<div class="tag">' . htmlentities('<') . '<span class="tag_name">' . $record_node .
                '</span>' . htmlentities('>') . '</div><div class="content">';
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
            //unset($element);
            $result['node'] = $node;
            //break;
        }

        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public static function getRowData($file_link, $index)
    {
        require_once _PS_MODULE_DIR_ . 'tvcore/models/file_types/TvcoreRecursiveDOMIterator.php';
        $dom = new DOMDocument; // create new DOMDocument instance
        $dom->load($file_link);       // load DOMDocument with XML data
        $xpath = new DOMXPath($dom);
        $dit = new RecursiveIteratorIterator(
            new TvcoreRecursiveDOMIterator($xpath->query('product')[$index]),
            RecursiveIteratorIterator::SELF_FIRST);

        $i = 0;
        foreach ($dit as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $value = self::getNodeValueByPath($node);
                $file_rows[$i] = [
                    'name' => $node->nodeName,
                    'path' => '/product' . substr($node->getNodePath(), strlen('/products/product[' . $index . ']')),
                    'depth' => $dit->getDepth() + 1,
                    'value' => $value['value'],
                    'cdata' => $value['cdata'],
                    'has_children' => 0,
                    'size' => 2 * strlen($node->nodeName) + 5 + strlen($value['value'])
                        + (int) $value['cdata'] + 12,
                ];

                ++$i;
            } elseif ($node->nodeType == 4) {
                --$i;
                $file_rows[$i]['cdata'] = 1;
                ++$i;
            }
        }

        $classes = $open = [];
        $expander = '';

        if ($file_rows[0]['size'] <= 60 && $file_rows[0]['has_children'] == 0) {
            $classes[] = 'short';
        } else {
            $expander .= '<div class="expander">-</div>';
        }

        $result = '<div class="element lvl_0" data-path="/product"><div class="expander">-</div>' .
            '<div class="tag">&lt;<span class="tag_name">product</span>&gt;</div><div class="content">' .
            '<div class="element lvl_1' . ' ' . implode(
                ' ',
                $classes
            ) . '" ' . 'data-path="' . $file_rows[0]['path'] . '">' . $expander .
            '<div class="tag">&lt;<span class="tag_name">' . $file_rows[0]['name'] . '</span>&gt;</div><div class="content">';

        for ($i = 1; $i < sizeof($file_rows); $i++) {
            $prev = $file_rows[$i - 1];
            $classes = [];
            $expander = '';

            if (isset($file_rows[$i + 1])) {
                $next_record = $file_rows[$i + 1];
                if ($next_record['depth'] == $file_rows[$i]['depth'] + 1) {
                    $file_rows[$i]['has_children'] = 1;
                }
            }

            if ($file_rows[$i]['size'] <= 60 && $file_rows[$i]['has_children'] == 0) {
                $classes[] = 'short';
            } else {
                $expander .= '<div class="expander">-</div>';
            }

            if ($prev['cdata'] == 1) {
                $value = '&#x3C;![CDATA[' . $prev['value'] . ']]&#x3E;';
            } else {
                $value = $prev['value'];
            }

            if ($file_rows[$i]['depth'] == $prev['depth']) {
                // The previous one should be closed
                $result .= $value . '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>' .
                    '<div class="element lvl_' . $file_rows[$i]['depth'] . ' ' . implode(' ', $classes) . '" ' .
                    'data-path="' . $file_rows[$i]['path'] . '">' . $expander .
                    '<div class="tag">&lt;<span class="tag_name">' . $file_rows[$i]['name'] . '</span>&gt;</div><div class="content">';
            } elseif ($file_rows[$i]['depth'] == $prev['depth'] + 1) {
                //$classes = [];
                // We save to the open array
                $open[] = '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>';

                $result .= '<div class="element lvl_' . $file_rows[$i]['depth'] . ' ' . implode(
                        ' ',
                        $classes
                    ) . '" ' . 'data-path="' . $file_rows[$i]['path'] . '">' . $expander .
                    '<div class="tag">&lt;<span class="tag_name">' . $file_rows[$i]['name'] . '</span>&gt;</div><div class="content">';
            } else {
                $result .= $value . '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>';
                // Its going up after descend
                // We count the steps
                $steps = $prev['depth'] - $file_rows[$i]['depth'];
                for ($last_i = 0; $last_i < $steps; ++$last_i) {
                    $last = array_key_last($open);
                    $result .= $open[$last];
                    unset($open[$last]);
                }

                $result .= '<div class="element lvl_' . $file_rows[$i]['depth'] . ' ' . implode(
                        ' ',
                        $classes
                    ) . '" ' . 'data-path="' . $file_rows[$i]['path'] . '">' . $expander .
                    '<div class="tag">&lt;<span class="tag_name">' . $file_rows[$i]['name'] . '</span>&gt;</div><div class="content">';
            }
        }

        $last_record = $file_rows[$i - 1];

        if ($last_record['cdata'] == 1) {
            $value = '&#x3C;![CDATA[' . $last_record['value'] . ']]&#x3E;';
        } else {
            $value = $last_record['value'];
        }

        $result .= $value . '</div><div class="tag">&lt;/<span class="tag_name">' . $last_record['name'] . '</span>&gt;</div></div>';

        $result .= '</div><div class="tag">&lt;<span class="tag_name">/product</span>&gt;</div></div>';

        return $result;
    }

    public static function getNodeValueByPath($node)
    {
        $i = 0;
        // Handle CDATA stuff
        if (isset($node->childNodes[1]->nodeValue)) {
            $i = 1;
        }

        return [
            'value' => $node->childNodes[$i]->nodeValue,
            'cdata' => $i,
        ];
    }
}
