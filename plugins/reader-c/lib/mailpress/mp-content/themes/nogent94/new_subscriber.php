<?php
/*
Template Name: new_subscriber
Subject: [<?php bloginfo('name');?>] <?php printf( 'En attente de : %s', '{{toemail}}'); ?>
*/

$this->build->_the_title = "Validation de votre adresse mail";

$_the_content = "Merci <a " . $this->classes('button', false) . "href='{{subscribe}}'>de confirmer</a> votre addresse mail.";
$_the_content .= '<br />';
$this->build->_the_content = $_the_content;

unset($this->args->unsubscribe);
$this->get_template_part('_mail');