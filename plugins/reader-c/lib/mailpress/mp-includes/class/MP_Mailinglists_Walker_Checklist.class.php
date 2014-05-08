<?php
class MP_Mailinglists_Walker_Checklist extends Walker 
{
	var $tree_type = MailPress_mailinglist::taxonomy;
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

	function start_lvl(&$output, $depth = 0, $args = array()) 
	{
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	function end_lvl(&$output, $depth = 0, $args = array() ) 
	{
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	function start_el(&$output, $mailinglist, $depth, $args) 
	{
		extract($args);

		$class = in_array( $mailinglist->term_id, $popular_mailinglists ) ? ' class="popular-mailinglist"' : '';
		$output .= "\n<li id='mailinglist-$mailinglist->term_id'$class>" . '<label for="in-mailinglist-' . $mailinglist->term_id . '" class="selectit"><input value="' . $mailinglist->term_id . '" type="checkbox" name="' . $args['input_name'] . '" id="in-mailinglist-' . $mailinglist->term_id . '"' . checked(in_array( $mailinglist->term_id, $selected_mailinglists ), true, false) . ' /> ' . esc_html( $mailinglist->name ) . '</label>';
	}

	function end_el(&$output, $mailinglist, $depth = 0, $args = array()) 
	{
		$output .= "</li>\n";
	}
}