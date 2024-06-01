<?php
/**
 * Core PrestaShop module - Cornelius
 *
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
        int $node_index,
    ) {
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
            //unset($element);
            $result['node'] = $node;
            //break;
        }

        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
