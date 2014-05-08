<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( 'Waiting for : %s', '{{toemail}}'); ?>
*/

$this->build->_the_title = 'Email validation';

$this->build->_the_content 	= sprintf('Please confirm your email address : %s', '{{subscribe}}');
$this->build->_the_content  .= "\n\n";

unset($this->args->unsubscribe);
$this->get_template_part('_mail');