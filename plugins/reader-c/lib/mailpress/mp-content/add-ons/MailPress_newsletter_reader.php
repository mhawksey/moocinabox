<?php
if (class_exists('MailPress_newsletter') && !class_exists('MailPress_newsletter_reader') )
{
/*
Plugin Name: MailPress_newsletter_reader
Description: Newsletters : Custom Post Type : reader (Course Reader) (<span style='color:red;'>required !</span> <span style='color:#D54E21;'>Newsletter</span> add-on) (<span style='color:red;'>sample add-on for test only</span>)
Version: 5.3
*/

class MailPress_newsletter_reader extends MP_newsletter_post_type_
{
	var $file	= __FILE__;

	var $post_type= 'reader';


}
new MailPress_newsletter_reader();
}