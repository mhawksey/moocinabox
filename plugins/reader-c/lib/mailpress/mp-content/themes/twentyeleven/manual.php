<?php
/*
Template Name: manual
*/

$this->get_header();
$this->args->newsletter = true;		// to tweak $this->the_content in manually newsletter with query_posts
$this->get_template_part('_loop');
$this->args->newsletter = false;

$this->get_footer();