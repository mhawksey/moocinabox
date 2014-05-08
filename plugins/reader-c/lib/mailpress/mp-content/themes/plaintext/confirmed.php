<?php
/*
Template Name: confirmed
Subject: [<?php bloginfo('name');?>] <?php printf('%s confirmed', '{{toemail}}'); ?>
*/

$this->build->_the_title = 'Congratulations !';

$this->build->_the_content = sprintf('You are now a subscriber of : %s.', ' [' . site_url() . ']');

$this->get_template_part('_mail');