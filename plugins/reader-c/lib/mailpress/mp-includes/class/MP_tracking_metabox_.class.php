<?php
abstract class MP_tracking_metabox_
{
	function __construct($title)
	{
		$this->title = $title;
		$this->type  = basename(dirname($this->file));

		add_filter('MailPress_tracking_metaboxes_register', 	array($this, 'register'), 8, 2);
		add_action('MailPress_tracking_add_meta_box', 		array($this, 'add_meta_box'), 8, 1);
	}

	function register($metaboxes, $type)
	{
		if ($type != $this->type) return $metaboxes;

		$metaboxes[$this->id]['title'] = $this->title;
		if (isset($this->parms)) $metaboxes[$this->id]['parms'] = $this->parms;
		ksort($metaboxes);
		return $metaboxes;
	}

	function add_meta_box($screen)
	{
		add_meta_box('tracking' . $this->id . 'div', $this->title, array($this, 'meta_box'), $screen, $this->context, '');
	}
}