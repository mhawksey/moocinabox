<?php
abstract class MP_Iframe_
{
	function __construct()
	{
		add_action('admin_print_styles', 		array($this, 'print_styles'));
		add_action('admin_print_scripts' , 		array($this, 'print_scripts'));

		add_filter('MailPress_form_email', 		array($this, 'form_email'), 8, 1);
		add_filter('MailPress_form_name', 		array($this, 'form_name'), 8, 1);

		add_filter('MailPress_form_imgloading', 	array($this, 'form_imgloading'), 8, 1);

		$this->init();

		$this->get_iframe();

		remove_filter('MailPress_form_imgloading',array($this, 'form_imgloading'), 8, 1);
	}

	function print_styles() {}
	function print_scripts() {}

	function form_email($x) { return $x; }
	function form_name($x) { return $x; }
	function form_imgloading($x) { return $x; }

	function get_header() {}
	function before() {}
	function after() {}
	function get_footer() {}

	function init() {}

	function get_iframe()
	{
		$this->get_header();
		$this->before();

		$attrs = '';
		if (isset($_GET) && !empty($_GET)) foreach($_GET as $k => $v) $attrs .= " $k=\"$v\"";

		echo do_shortcode("[mailpress$attrs]");

		$this->after();
		$this->get_footer();
	}
}