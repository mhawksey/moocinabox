<?php
/*
Template Name: confirmed
Subject: [<?php bloginfo('name');?>] <?php printf('Abonnement de %s', '{{toemail}}'); ?>
*/

$this->build->_the_title = 'F&eacute;licitations !';

$this->build->_the_content =  "Vous &ecirc;tes maintenant abonn&eacute; &agrave; : <a " . $this->classes('button', false) . " href='" . get_option('siteurl') . "'>" . get_option('blogname') . "</a><br />";

$this->get_template_part('_mail');