<?php
/*
Template Name: retrieve_pwd
*/
$user = $this->args->advanced->user;
$url  = $this->args->advanced->url;

$this->build->_the_title = 'Password reset';

$_message = __('Someone requested that the password be reset for the following account:') . "<br />\r\n<br />\r\n";
$_message .= $url['site'] . "<br />\r\n<br />\r\n";
$_message .= sprintf(__('Username: %s'), $user->user_login) . "<br />\r\n<br />\r\n";
$_message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "<br />\r\n<br />\r\n";	
$_message .= __('To reset your password, visit the following address:') . "<br />\r\n<br />\r\n";	
$_message .= $url['reset'] . "<br />\r\n";
                
$this->build->_the_content  = $_message;

$this->build->_the_actions  = "<a " . $this->classes('button', false) . " href='{$url['reset']}'>"	. __('Reset') . "</a>";

$this->get_template_part('_mail');