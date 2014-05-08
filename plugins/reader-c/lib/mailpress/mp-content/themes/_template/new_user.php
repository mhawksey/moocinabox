<?php
/*
Template Name: new_user
*/
$user = $this->args->advanced->user;

if (isset($this->args->advanced->admin))
{
	$this->build->_the_title = 'New User';

	$_the_content  = sprintf(__('Username: %s'), stripslashes($user->user_login) );
	$_the_content .= "<br />\r\n";
	$_the_content .= sprintf(__('E-mail: %s'),   stripslashes($user->user_email) );
	$_the_content .= "<br />\r\n<br />\r\n";
	$this->build->_the_content = $_the_content;
}
else
{
	$this->build->_the_title = 'Welcome !';

	$_the_content  = sprintf(__('Username: %s'), stripslashes($user->user_login) );
	$_the_content .= "<br />\r\n";
	$_the_content .= sprintf(__('Password: %s'), $user->plaintext_pass ) ;
	$_the_content .= "<br />\r\n<br />\r\n";
	$this->build->_the_content = $_the_content;

	$this->build->_the_actions  = "<a " . $this->classes('button', false) . " href='" . wp_login_url() . "'>" . __('Log in') . "</a><br />\r\n";
}

$this->get_template_part('_mail');