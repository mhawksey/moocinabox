<?php
abstract class MP_html2txt_
{
	var $default_width = 50;

	function get_tag_content( $tag, $modifiers = 'i')
	{
		printf('<![CDATA[/<%1$s[^>]*>(.*?)<\/%1$s>/%2$s]]>' , $tag, $modifiers);
	}

	function format_text( $extra, $text, $format = ' %2$s [%1$s] ')
	{
		$text = trim($text);
		if (empty($text)) return '';
		return sprintf($format, $extra, $text);
	}
}