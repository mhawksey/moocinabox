<?php
class MP_Autoresponder_event_new_commenter extends MP_autoresponder_event_
{
	var $id    = 2;
	var $event = 'MailPress_new commenter';
}
new MP_Autoresponder_event_new_commenter(__('New commenter', MP_TXTDOM));