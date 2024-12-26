<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
class TvcoreXml
{
    protected string $prefix = 'col';

    public static function getSlice($link, $tag, $filter, $ignore, $step)
    {
        $result = [];
        $dom = new DOMDocument();
        $reader = new XMLReader();
        $reader->open($link);

        $count = $index = 0;
        $min_index = $ignore * $step;
        while ($reader->read() && $count < $step) {
            if ($reader->nodeType == \XMLReader::ELEMENT and $reader->name == $tag) {
                while ($index < $min_index) {
                    $reader->next($tag);
                    ++$index;
                }
                ++$count;
                $node = $reader->expand($dom);
                $result[] = $node;

                // clearing current element
                unset($node);
            }
        }

        return $result;
    }

    private static function getNodeObject(string $link, int $node_index, string $tag)
    {
        $reader = new XMLReader();
        $reader->open($link, null, LIBXML_NOCDATA);

        $index = 1;
        while ($reader->read()) {
            if ($reader->nodeType == \XMLReader::ELEMENT and $reader->name == $tag) {
                while ($index < $node_index) {
                    ++$index;
                    continue 2;
                }
                $dom = new DOMDocument();
                $node = $reader->expand($dom);
                $xpath = new DOMXPath($dom);
                return [
                    'dom' => $dom,
                    'node' => $node,
                    'xpath' => $xpath,
                ];
            }
        }
    }

    private static function getSubNode(mixed $node, RecursiveIteratorIterator $dit)
    {
        $has_children = 0;
        $cdata = 0;

        if ($node->childElementCount > 0) {
            $has_children = 1;

            if ($node->childNodes[0]->nodeType == 4) {
                $cdata = 1;
            }
        }

        $size = 2 * strlen($node->nodeName) + 5 + strlen($node->nodeValue)
            + $cdata + 12;

        $class = $expander = '';
        if ($size <= 45
            && !$has_children
            && $node->nodeName != 'barcode'
        ) {
            $class = ' short short-' . $size;
        } else {
            $expander = '<div class="expander">-</div>';
        }

        return [
            'name' => $node->nodeName,
            'path' => $node->getNodePath(),
            'depth' => $dit->getDepth() + 1,
            'value' => $node->nodeValue,
            'cdata' => $cdata,
            'has_children' => $has_children,
            'size' => $size,
            'class' => $class,
            'expander' => $expander,
        ];
    }

    public static function toJson(string $xml_path, string $json_path)
    {
        $xml = simplexml_load_file(
            $xml_path,
            'SimpleXMLElement',
            LIBXML_NOCDATA
        );
        $arr = (array) $xml;
        $json_file = $json_path;
        $json_string = str_replace(
            [
                '\n            ',
            ],
            [
                '',
            ],
            json_encode($arr, JSON_UNESCAPED_UNICODE)
        );

        file_put_contents($json_file, $json_string);
    }

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
     * @param string $link
     * @param string $node_tag
     * @param int $node_index
     * @return string
     */
    public static function getPrettyPrintedNode(
        string $link,
        string $node_tag = 'product',
        int $node_index = 0,
    ) {
        $obj = self::getNodeObject($link, $node_index, $node_tag);
        //var_dump($obj);
        require_once __DIR__ . '/TvcoreRecursiveDOMIterator.php';
        $dit = new RecursiveIteratorIterator(
            new TvcoreRecursiveDOMIterator($obj['node']),
            RecursiveIteratorIterator::SELF_FIRST);
        $i = 0;
        foreach ($dit as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $file_rows[$i] = self::getSubNode($node, $dit);

                ++$i;
            }
        }
        //print('<pre>' . print_r($file_rows, true) . '</pre>');
        $result = '<div class="element lvl_0" data-path="/product"><div class="expander">-</div>' .
            '<div class="tag">&lt;<span class="tag_name">product</span>&gt;</div><div class="content">' .
            '<div class="element lvl_1' . $file_rows[0]['class'] . '" data-path="' . $file_rows[0]['path'] . '">' .
            $file_rows[0]['expander'] .
            '<div class="tag">&lt;<span class="tag_name">' . $file_rows[0]['name'] .
            '</span>&gt;</div><div class="content">';

        for ($i = 1; $i < sizeof($file_rows); ++$i) {
            $prev = $file_rows[$i - 1];

            if ($prev['cdata'] == 1) {
                $value = '&#x3C;![CDATA[' . $prev['value'] . ']]&#x3E;';
            } else {
                $value = $prev['value'];
            }

            if ($file_rows[$i]['depth'] == $prev['depth']) {
                // The previous one should be closed
                $result .= $value . '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>' .
                    '<div class="element lvl_' . $file_rows[$i]['depth'] . $file_rows[$i]['class'] . '" ' .
                    'data-path="' . $file_rows[$i]['path'] . '">' . $file_rows[$i]['expander'] .
                    '<div class="tag">&lt;<span class="tag_name">' . $file_rows[$i]['name'] . '</span>&gt;</div><div class="content">';
            } elseif ($file_rows[$i]['depth'] == $prev['depth'] + 1) {
                // We save to the open array
                $open[] = '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>';

                $result .= '<div class="element lvl_' . $file_rows[$i]['depth'] . $file_rows[$i]['class'] .
                    '" data-path="' . $file_rows[$i]['path'] . '">' . $file_rows[$i]['expander'] .
                    '<div class="tag">&lt;<span class="tag_name">' . $file_rows[$i]['name'] . '</span>&gt;</div><div class="content">';
            } else {
                $result .= $value . '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>';
                // It's going up after descend - We count the steps
                $steps = $prev['depth'] - $file_rows[$i]['depth'];
                for ($last_i = 0; $last_i < $steps; ++$last_i) {
                    $last = array_key_last($open);
                    $result .= $open[$last];
                    unset($open[$last]);
                }

                $result .= '<div class="element lvl_' . $file_rows[$i]['depth'] . $file_rows[$i]['class'] . '" data-path="' . $file_rows[$i]['path'] . '">' . $file_rows[$i]['expander'] .
                    '<div class="tag">&lt;<span class="tag_name">' . $file_rows[$i]['name'] . '</span>&gt;</div><div class="content">';
            }
        }

        $last_record = $file_rows[$i - 1];

        if ($last_record['cdata'] == 1) {
            $value = '&#x3C;![CDATA[' . $last_record['value'] . ']]&#x3E;';
        } else {
            $value = $last_record['value'];
        }

        //$value = '&#x3C;![CDATA[' . $last_record['value'] . ']]&#x3E;';

        $result .= $value . '</div><div class="tag">&lt;/<span class="tag_name">' . $last_record['name'] . '</span>&gt;</div></div>';

        $result .= '</div><div class="tag">&lt;<span class="tag_name">/product</span>&gt;</div></div>';

        return $result;
    }

    public static function getRowData($file_link, $index)
    {
        require_once _PS_MODULE_DIR_ . 'tvcore/models/file_types/TvcoreRecursiveDOMIterator.php';
        $dom = new DOMDocument('1.0', 'utf-8');
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

        if ($file_rows[0]['size'] <= 45
            && $file_rows[0]['has_children'] == 0
            && $file_rows[0]['name'] != 'barcode') {
            $classes[] = 'short size-' . $file_rows[$i]['size'];
        } else {
            $expander .= '<div class="expander">-</div>';
        }

        $result = '<div class="element lvl_0" data-path="/product"><div class="expander">-</div>' .
            '<div class="tag">&lt;<span class="tag_name">product</span>&gt;</div><div class="content">' .
            '<div class="element lvl_1 ' . implode(
                ' ',
                $classes
            ) . '" data-path="' . $file_rows[0]['path'] . '">' . $expander .
            '<div class="tag">&lt;<span class="tag_name">' . $file_rows[0]['name'] . '</span>&gt;</div><div class="content">';

        for ($i = 1; $i < sizeof($file_rows); ++$i) {
            $prev = $file_rows[$i - 1];
            $classes = [];
            $expander = '';

            if (isset($file_rows[$i + 1])) {
                $next_record = $file_rows[$i + 1];
                if ($next_record['depth'] == $file_rows[$i]['depth'] + 1) {
                    $file_rows[$i]['has_children'] = 1;
                }
            }

            if ($file_rows[$i]['size'] <= 45
                && $file_rows[$i]['has_children'] == 0
                && $file_rows[$i]['name'] != 'barcode') {
                $classes[] = 'short size-' . $file_rows[$i]['size'];
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
                // We save to the open array
                $open[] = '</div><div class="tag">&lt;/<span class="tag_name">' . $prev['name'] . '</span>&gt;</div></div>';

                $result .= '<div class="element lvl_' . $file_rows[$i]['depth'] . ' ' .
                    implode(' ', $classes) . '" data-path="' . $file_rows[$i]['path'] . '">' . $expander .
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

                $result .= '<div class="element lvl_' . $file_rows[$i]['depth'] . ' ' .
                    implode(' ', $classes) . '" data-path="' . $file_rows[$i]['path'] . '">' . $expander .
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

    public static function getNodesCount(string $link, string $tag, string $filter = '')
    {
        $count = 0;
        $dom = new DOMDocument();
        $xpath = new DOMXPath($dom);
        $reader = new XMLReader();
        $reader->open($link);
        while ($reader->read()) {
            if ($reader->nodeType == \XMLReader::ELEMENT && $reader->name == $tag) {
                $node = $reader->expand($dom);
                if ($xpath->query($tag . $filter, $node)) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    public static function createFilteredData(
        string $feed_path,
        string $node_path,
        string $filter,
        string $destination
    ): void {
        $reader = new XMLReader();
        $dom = new DOMDocument();
        $reader->open($feed_path);
        $filtered_doc = new DOMDocument('1.0', 'utf-8');
        $root = $filtered_doc->createElement('root');
        while ($reader->read()) {
            if ($reader->nodeType == \XMLReader::ELEMENT and $reader->name == $node_path) {
                $node = $reader->expand($dom);
                if ((new DOMXPath($dom))->query($filter, $node)->length > 0) {
                    $tempNode = $filtered_doc->importNode($node, true);
                    $root->appendChild($tempNode);
                }
            }
        }
        $reader->close();
        $filtered_doc->appendChild($root);
        $filtered_doc->save($destination);
        // $data_filtered_xml_path = getcwd() . '/uploads/' . $profile . '/data_filtered.xml';
    }
}
