<?php
/*
Template Name: new_subscriber
*/

$this->build->_the_title = "Validation de votre adresse mail";

$_the_content  = "Merci de confirmer votre addresse mail.";
$_the_content .= "\n";
$_the_content .= '[{{subscribe}}]';
$this->build->_the_content = $_the_content;

unset($this->args->unsubscribe);
$this->get_template_part('_mail');