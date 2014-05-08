<?php
class MP_Mailinglists_Walker_Array extends Walker 
{
	var $tree_type = MailPress_mailinglist::taxonomy;
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	
	function start_el(&$output, $mailinglist, $depth, $args) 
	{
		$pad = str_repeat('&#160;', $depth * 3);
		$x = 'MailPress_mailinglist~' . $mailinglist->term_id;
		$output [$x] = $pad.$mailinglist->name;
	}
}