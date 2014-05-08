<?php
class MP_Xml
{
	function __construct($xml)
	{
		$this->dom = New DOMDocument();
		$this->dom->loadXML($xml);
		$this->root = $this->dom->documentElement;

		$this->object = new stdClass();
		$this->getElement($this->root, $this->object);
	}

	function get_xml($xmlObject) 
	{
		unset($xmlObject->textValue);
		$dom = new DomDocument();
		$root = $dom->createElement($xmlObject->name);
		$dom->appendChild($root);
		$this->setElement($dom, $xmlObject, $root);
		return $dom->saveXML();
	}

	public static function sanitize($string, $parent = false)
	{
		$xml_header = '<?xml version="1.0"?>';

		if ($parent) $parent = trim($parent);

		$xml = '';
		if (strpos($string, '<?xml') === false) 	$xml .= $xml_header;
		if ($parent)					$xml .= "<$parent>";
		$xml .= trim($string);
		if ($parent)					$xml .= "</$parent>";

		if (simplexml_load_string($xml)) return $xml;

		$xml = str_replace($string, utf8_encode($string), $xml);
		if (simplexml_load_string($xml)) return $xml;

		return false;
	}

/* from http://eusebius.developpez.com/php5dom/ */

	function getElement($dom_element, $object_element)
	{
		$object_element->name = $dom_element->nodeName;
		$object_element->textValue = (isset($dom_element->firstChild->nodeValue)) ? trim($dom_element->firstChild->nodeValue) : '';
		if ($dom_element->hasAttributes()) 
		{
			$object_element->attributes = array();
			foreach($dom_element->attributes as $attName=>$dom_attribute) $object_element->attributes[$attName] = $dom_attribute->value;
		}
		if ($dom_element->childNodes->length > 1) 
		{
			$object_element->children = array();
			foreach($dom_element->childNodes as $dom_child) 
			{
				if ($dom_child->nodeType == XML_ELEMENT_NODE) 
				{
					$child_object = new stdClass();
					$this->getElement($dom_child, $child_object);
					array_push($object_element->children, $child_object);
				}
			}
		}
	}

	function setElement($dom_document, $object_element, $dom_element)
	{
		if (isset($object_element->textValue)) 
		{
			$cdata = $dom_document->createTextNode($object_element->textValue);
			$dom_element->appendChild($cdata);
		}
		if (isset($object_element->attributes)) foreach($object_element->attributes as $attName=>$attValue) $dom_element->setAttribute($attName, $attValue);
		if (isset($object_element->children)) 
		{
			foreach($object_element->children as $childObject) 
			{
				$child = $dom_document->createElement($childObject->name);
				$this->setElement($dom_document, $childObject, $child);
				$dom_element->appendChild($child);			
			}
		}	
	}
}