<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( 'Waiting for : %s', '{{toemail}}'); ?>
*/

$this->build->_the_title = 'Email validation';

$_the_content = sprintf('Please confirm your email address : %s', sprintf('<a ' . $this->classes('button', false) . 'href="{{subscribe}}">%s</a>', 'Confirm'));
$_the_content .= '<br />';
$this->build->_the_content = $_the_content;

unset($this->args->unsubscribe);
$this->get_template_part('_mail');