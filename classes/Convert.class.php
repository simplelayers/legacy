<?php

class Convert {
    /* 	function maskErrors() {}
      set_error_handler('maskErrors');
      $dom->loadXml($xml);
      restore_error_handler();
      $dom = new DOMDocument('1.0', 'iso-8859-1');
     */

    public function xmlToPhp($xmlstr) {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);
        return Array($doc->documentElement->tagName => $this->domnode_to_array($doc->documentElement));
    }

    private function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if (isset($child->tagName)) {
                        $t = '' . $child->tagName;


                        if (!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        array_push($output[$t], $v);
                    } elseif ($v) {
                        $output['value'] = (string) $v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode)
                            $a[$attrName] = (string) $attrNode->value;
                        $output['_attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '_attributes')
                            $output[$t] = $v[0];
                    }
                }
                break;
        }
        return $output;
    }

    function WritePhpToXml($array, $nodeName = null) {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        if ($nodeName) {
            echo '<' . $nodeName . '>';
        }
        foreach ($array as $key => $value) {
            $this->WriteXMLNode($key, $value);
            /* $key = $this->SanitizeXMLNodeName($key);
              echo '<' . $key;
              echo $this->WriteXMLNode($value, true);
              echo '</' . $key . '>';
             *
             */
        }
        if ($nodeName) {
            echo '</' . $nodeName . '>';
        }
    }

    public function SanitizeXMLNodeName($name) {
        $name = str_replace(' ', '_', $name);
        return $name;
    }

    public function WriteXMLNode($key, $value) {
        $isInt = is_numeric($key) || ($key === 'value');
        if(!$isInt) {
            echo '<' . $key;
        }
        if (is_array($value)) {
            if (isset($value['_attributes'])) {
                foreach ($value['_attributes'] as $attName => $attValue) {
                    $attName = $this->SanitizeXMLNodeName($attName);
                    echo ' ' . $attName . '=' . '"' . $attValue . '"';
                }
            }
            unset($value['_attributes']);
        }
        if(!$isInt) {
            echo '>';
        }

        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $subKey = $this->SanitizeXMLNodeName($subKey);
                $this->WriteXMLNode($subKey,$subValue);
            }
        } elseif (is_object($value)) {
            echo '<json><![CDATA['.json_stringify($value).']]></json>';
        } else {
            echo htmlspecialchars($value, \ENT_XML1, 'UTF-8');
        }
        if(!$isInt) {
            echo '</' . $key . '>';
        }
    }

    public function phpToXml($array) {
        foreach ($array as $key => $value) {
            $xml = new SimpleXMLElement('<' . $key . '/>');
            $this->array_to_xml($value, $xml);
            return $xml->asXML();
        }
    }

    private function array_to_xml($array, &$parent, &$grandParent = null, $parKey = null) {
        foreach ($array as $key => $child) {
            if ($key != '_attributes' || is_numeric($key)) {
                if ($grandParent !== null && (is_numeric($key)))
                    $key = $parKey;
                if (is_array($child)) {
                    if ($child["0"] === null) {
                        $childXML = $parent->addChild($key);
                        $this->array_to_xml($child, $childXML, $parent, $key);
                    } else {
                        $this->array_to_xml($child, $parent, $parent, $key);
                    }
                } else {
                    $parent->addChild($key, $child);
                }
            } else {
                foreach ($child as $attrName => $attrValue) {
                    $parent->addAttribute($attrName, $attrValue);
                }
            }
        }
    }

    public function phpToJson($php) {
        return json_encode($php);
    }

    public function jsonToPhp($json) {
        return json_decode($json);
    }

    public function xmlToJson($xml) {
        return $this->phpToJson($this->xmlToPhp($xml));
    }

    public function jsonToXml($json) {
        return $this->phpToXml($this->jsonToPhp($json));
    }

}

?>
