<?php
class MP_Autoresponder_event_activate_mp_user extends MP_autoresponder_event_
{
	var $id    = 1;
	var $event = 'MailPress_activate_user';
}
new MP_Autoresponder_event_activate_mp_user(__('Subscription activated', MP_TXTDOM));