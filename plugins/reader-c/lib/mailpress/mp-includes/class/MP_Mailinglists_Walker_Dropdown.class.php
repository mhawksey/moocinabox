<?php
class MP_Mailinglists_Walker_Dropdown extends Walker 
{
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	
	function start_el(&$output, $mailinglist, $depth, $args) 
	{
		$pad = str_repeat('&#160;', $depth * 3);

		$output .= "\t<option value=\"".$mailinglist->term_id."\"";
		if ( $mailinglist->term_id == $args['selected'] ) $output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.$mailinglist->name;
		if ( $args['show_count'] ) $output .= '&#160;&#160;('. $mailinglist->count .')';
		if ( $args['show_last_update'] ) 
		{
			$format = 'Y-m-d';
			$output .= '&#160;&#160;' . gmdate($format, $mailinglist->last_update_timestamp);
		}
		$output .= "</option>\n";
	}
}