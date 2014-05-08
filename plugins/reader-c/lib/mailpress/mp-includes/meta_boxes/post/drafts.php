<?php
global $wpdb;

// drafts
$results = $wpdb->get_results( $wpdb->prepare( "SELECT id, subject FROM $wpdb->mp_mails WHERE status = %s ORDER BY subject ASC;", 'draft' ) );
if (empty($results))
{
	_e('No results found.', MP_TXTDOM);
	return;
}
$x = new MP_Mail();
foreach($results as $draft) $drafts[$draft->id] = $x->viewsubject($draft->subject, $draft->id, $draft->id);
unset($results);

// posts in drafts
$post_drafts = MP_Post::get_term_objects($post->ID);

?>
<div id='mpdrafts'>
	<ul id='mpdraftchecklist' class='list:mpdraft mpdraftchecklist' data-wp-lists='list:mpdraft'>
<?php

if (!empty($post_drafts))
{
	foreach($post_drafts as $id)
	{
		if (!isset($drafts[$id])) continue;
		echo MailPress_post::get_draft_row($id, $drafts[$id]);
	}
}
?>
	</ul>
</div>
<div id='mpdraft-adder' class='wp-hidden-children'>
	<h4>
		<a id='mpdraft-add-toggle' class='hide-if-no-js' href='#mpdraft-add'><?php _e('+ Add New Draft', MP_TXTDOM); ?></a> 
	</h4>
	<p id='mpdraft-add' class='wp-hidden-child'>
		<select id='newmpdraft' name='newmpdraft' style='width:100%;font-size:11px;height:2em;padding:2px;'>
<?php
foreach( $drafts as $k => $v )
{
?>
			<option id="newmpdraft-<?php echo $k; ?>" value="<?php echo $k; ?>"><?php echo $v; ?></option>
<?php
}
?>
		</select>
		<input type="button" id="mpdraft-add-submit" class="add:mpdraftchecklist:mpdraft-add button" data-wp-lists="add:mpdraftchecklist:mpdraft-add button" value="<?php esc_attr_e( 'Add', MP_TXTDOM  ); ?>" tabindex="3" />
<?php	wp_nonce_field( 'add-mpdraft', '_ajax_nonce', false ); ?>
		<span id="mpdraft-ajax-response"></span>
	</p>
</div>