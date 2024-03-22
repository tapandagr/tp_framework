<?php

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

        //header('Content-Type: text/plain');
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
            //echo '<br>' . $this->prefix . $key;
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
}
