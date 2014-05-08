<?php
/*
Template Name: confirmed
*/

$this->build->_the_title = 'Félicitations !';

$_the_content  = "Vous êtes maintenant abonné à : " . get_option('blogname');
$_the_content .= "\n";
$_the_content .= '[' . site_url() . ']';
$this->build->_the_content = $_the_content;

$this->get_template_part('_mail');