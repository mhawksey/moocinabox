<?php
/*
Template Name: retrieve_pwd
*/
$user = $this->args->advanced->user;
$url  = $this->args->advanced->url;

$this->build->_the_title = 'Password reset';

$_message = __('Someone requested that the password be reset for the following account:') . "\n\n";
$_message .= $url['site'] . "\n\n";
$_message .= sprintf(__('Username: %s'), $user->user_login) . "\n\n";
$_message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\n\n";	
$_message .= __('To reset your password, visit the following address:') . "\n\n";	
$_message .= $url['reset'] . "\n\n";
                
$this->build->_the_content  = $_message;

$this->build->_the_actions 	= __('Reset') . " [{$url['reset']}]\r\n";

$this->get_template_part('_mail');