<?php
abstract class MP_autoresponder_event_mailinglist_ extends MP_autoresponder_event_
{
	function to_do($autoresponder, $args)
	{
		$this->mp_user_id = $args['mp_user_id'];

		return ($autoresponder->description['settings']['mailinglist_id'] == $args['mailinglist_id']);
	}

	function settings_form($settings)
	{
		$mailinglist_id = (isset($settings['mailinglist_id'])) ? $settings['mailinglist_id'] : get_option(MailPress_mailinglist::option_name_default);
?>
							<label for='autoresponder_mailinglist_<?php echo $this->id; ?>'><?php _e('Mailing list', MP_TXTDOM) ?></label>
<?php		
		MP_Mailinglist::dropdown(array('name' => "description[settings][{$this->id}][mailinglist_id]", 'htmlid' => "autoresponder_mailinglist_{$this->id}", 'selected' => $mailinglist_id, 'hierarchical' => true, 'orderby' => 'name', 'hide_empty' => '0'));
?>
							<p><?php _e('For that mailinglist', MP_TXTDOM) ?></p>
<?php
	}
}