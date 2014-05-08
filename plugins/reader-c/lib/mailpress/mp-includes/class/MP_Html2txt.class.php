<?php
/* based on http://www.chuggnutt.com/html2text.php */
class MP_Html2txt extends MP_html2txt_
{
	var   $files = array();

	function __construct( $files = false, $args_file = 'args', $path = false , $debug = false )
	{
	// path for xml files
		$this->path = ($path) ? $path : MP_CONTENT_DIR . 'advanced/html2txt/';

	// debug
		$this->debug = $debug;

	// _parms
		$xml = $this->get_xml( $args_file );

		if (isset($xml->width)) 	$this->width = (int) $xml->width;
		if (!isset($this->width))	$this->width = (isset($this->default_width)) ? $this->default_width : 80;

		if (!$files && isset($xml->files)) foreach($xml->files->file as $file) $files[] = (string) $file;

		$this->s = $this->r = array();
		$xml2 = $this->get_xml($files);
		foreach($xml2->item as $item) foreach($item as $k => $v) $this->{$k}[] = (string) $v;
		unset($xml, $xml2);
	}

	function get_text( $text, $width = false)
	{
		if (empty($text)) return $text;

		if (false === $width)	$width = $this->width;

		switch ($this->debug)
		{
			case 1 :
				foreach($this->s as $k => $v)
				{
					$debug_text = preg_replace( $this->s[$k], $this->r[$k], $text, -1, $count) ;
					if ( $count ) file_put_contents($this->path . "_gen_repl_.txt" , "\n$@$ | $k | ($count) | {$this->s[$k]} | {$this->r[$k]} |\n\n**before** | $text \n\n**after ** | $debug_text \n", FILE_APPEND);
					$text = $debug_text;
				}
			break;
			case 2 :
				echo '<div><table>';
				foreach($this->s as $k => $v)
				{
					$debug_text = preg_replace( $this->s[$k], $this->r[$k], $text, -1, $count) ;
					if ( $count ) echo "<tr><td>$@$<b>$k</b> ($count)</td><td><b>" . htmlentities($this->s[$k]) . "</b><td><b>" . htmlentities($this->r[$k]) . "</b></td></tr><tr><td colspan='3'>" . wp_text_diff( $text, $debug_text ) . "<br /><hr /></td></tr>"; 
					flush();
					$text = $debug_text;
				}
				echo '</table></div>';
			break;
			default :
				$text = preg_replace($this->s, $this->r, $text);
			break;
		}

		$text = ($width) ? wordwrap($text, $width) : $text;

		if ($this->debug) file_put_contents($this->path . '_gen_text.txt' , $text, FILE_APPEND);

		return $text;
	}

// get xml file

	function get_xml( $files = false )
	{
		if (!is_array($files)) $files = array($files);

		$x = '';
		foreach($files as $file)
		{
			if (!is_file($this->path . "$file.xml")) continue;
			ob_start();
				include( $this->path . "$file.xml" );
				$x .= trim(ob_get_contents());
			ob_end_clean();
		}
		$x = '<?xml version="1.0" encoding="UTF-8"?><mailpress>' . $x . '</mailpress>';

		if ($this->debug) file_put_contents($this->path . '_gen_xml_.txt', $x, FILE_APPEND);

		return simplexml_load_string( $x, 'SimpleXMLElement', LIBXML_NOCDATA );
	}
}