<?php
abstract class MP_oembed_provider_
{
	function __construct()
	{
		add_filter('MailPress_oembed_providers_data2html_' . $this->id,	array($this, 'data2html'), 8, 3);
	}
}