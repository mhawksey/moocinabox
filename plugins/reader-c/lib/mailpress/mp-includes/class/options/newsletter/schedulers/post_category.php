<?php
class MP_Newsletter_scheduler_post_category extends MP_newsletter_scheduler_post_
{
	public $id        = 'post_category';
	public $post_type = 'post';
	public $taxonomy  = 'category';
}
new MP_Newsletter_scheduler_post_category(sprintf(__('Each %s', MP_TXTDOM), 'post/category'));