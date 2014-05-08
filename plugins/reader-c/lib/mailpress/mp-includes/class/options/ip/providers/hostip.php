<?php
class MP_Ip_hostip extends MP_ip_provider_
{
	var $id 	= 'hostip';
	var $url	= 'http://api.hostip.info/get_xml.php?ip=%1$s&position=true';
	var $credit	= 'http://www.hostip.info/';
	var $type 	= 'xml';

	function content($valid, $content)
	{
		if (strpos($content, '<countryAbbrev>XX</countryAbbrev>')) return false;
		if (strpos($content, '<!-- Co-ordinates are unavailable -->')) return false;
		return $valid;
	}

	function data($content, $ip)
	{
		$html = '';
		try 
		{
			set_error_handler(array($this, 'HandleXmlError'));
			$dom = New DOMDocument();
			$dom->loadXML($content);
			restore_error_handler();
		}
		catch (DOMException $e) 
		{
			return false;
		}

		$x = $this->parse_node($dom, 'Hostip');
		if ($x->nodeName == 'Hostip')
		{
			$h = $this->parse_node($x, 'countryAbbrev');
			$country = $h->nodeValue;

			$h = $this->parse_node($x, 'name');
			$html .= "<p style='margin:3px;'><b>City</b> : " . $h->nodeValue . "</p>";
			if ('US' == strtoupper($country)) $subcountry = substr($h->nodeValue, strlen($h->nodeValue)-2, 2);

			$h = $this->parse_node($x, 'countryName');
			$html .= "<p style='margin:3px;'><b>Country</b> : " . $h->nodeValue . "</p>";

			$h = $this->parse_node($x, 'coordinates');
			$lnglat = explode(',', $h->nodeValue);
			if (count($lnglat) < 2) return false;
			$geo['lat'] = $lnglat[1];
			$geo['lng'] = $lnglat[0];
		}
		return $this->cache_custom($ip, $geo, strtoupper(substr($country, 0, 2)), strtoupper($subcountry), $html);
	}

	function parse_node($node, $tagname) 
	{
		$xs = $node->getElementsByTagname($tagname); 
		foreach ($xs as $x) {};
		return $x;
	}

	function HandleXmlError($errno, $errstr, $errfile, $errline)
	{
		if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
			throw new DOMException($errstr);
		else
			return false;
	}
}
new MP_Ip_hostip();