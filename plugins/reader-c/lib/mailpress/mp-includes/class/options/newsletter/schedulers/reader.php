<?php
class MP_Newsletter_scheduler_reader extends MP_newsletter_scheduler_post_
{
	public $id        = 'reader';
	public $post_type = 'reader';
}
new MP_Newsletter_scheduler_reader(sprintf(__('Each %s', MP_TXTDOM), 'reader'));