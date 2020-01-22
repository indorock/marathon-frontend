<?php

class XPath_Query{
    
    private $doc;
    private $xpath;

    public function __construct($file){
        $this->doc = new DOMDocument();
        $this->doc->load($file);
        $this->xpath = new DOMXPath($this->doc);
    }
    
    public function get_nodelist($query, $context = null){
        if($context == null) $context = $this->doc->documentElement;
        return $this->xpath->query($query, $context);
    }

    public function get_node($query, $context = null){
        if($context == null) $context = $this->doc->documentElement;
        return $this->xpath->query($query, $context)->item(0);
    }
    
    public function get_value($query, $context = null){
        if($context == null) $context = $this->doc->documentElement;
         return trim($this->xpath->query($query, $context)->item(0)->nodeValue);
    }
}