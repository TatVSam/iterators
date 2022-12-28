<?php

function getAttributes(\DOMNode $element)
{
    $attributes = array();
    foreach ($element->attributes as $attribute) {
        $attributes[$attribute->nodeName] = $attribute->nodeValue;
    }
    return $attributes;
}



class RecursiveDOMIterator extends RecursiveArrayIterator {
    
    public function __construct($node) {
        parent::__construct(iterator_to_array($node->childNodes));
    }
    public function getChildren() {
        return new self($this->current());
    }
    public function hasChildren() {
        return $this->current()->hasChildNodes();
    }
 
}


$dom = new DOMDocument();
$content = file_get_contents("file.html");
$content = str_replace("&nbsp;", "@nbsp;", $content);

libxml_use_internal_errors(true);
$dom->loadHTML($content);
libxml_use_internal_errors(false);

$nodes = new RecursiveIteratorIterator(
    new RecursiveDOMIterator($dom),
    RecursiveIteratorIterator::SELF_FIRST);


foreach($nodes as $node) {

    if($node->nodeName == "title") {
        $node->parentNode->removeChild($node);
    } 

   if($node->nodeName == "meta") {
        $attr = getAttributes($node);
        if (!empty($attr["name"]) and (($attr["name"] == "description") or ($attr["name"] == "keywords")))
        $node->parentNode->removeChild($node);
    }   
}



$html = $dom->saveHTML($dom->documentElement);
$html = str_replace("@nbsp;","&nbsp;", $html);
$html = str_replace("%20"," ", $html);
file_put_contents("result.html", $html);