<?php
abstract class MP_connection_
{
	function __construct() 
	{
// for connection type & settings
		add_filter('MailPress_Swift_Connection_type', 						array($this, 'Swift_Connection_type'), 8, 1);
// for connection 
		add_filter('MailPress_Swift_Connection_' . $this->Swift_Connection_type, 	array($this, 'connect'), 8, 2);
	}

	function Swift_Connection_type($x) { return $this->Swift_Connection_type; }
}