<?php
class MP_Newsletter_scheduler_post extends MP_newsletter_scheduler_post_
{
	public $id 		= 'post';
	public $post_type 	= 'post';
}
new MP_Newsletter_scheduler_post(sprintf(__('Each %s', MP_TXTDOM), 'post'));