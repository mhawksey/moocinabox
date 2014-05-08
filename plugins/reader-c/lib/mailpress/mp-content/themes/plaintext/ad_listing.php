<?php
/*
Template Name: ad_listing
*/

while (have_posts()) : the_post();

	echo "\n";

	$title = $this->get_the_title(); 
	$title = trim($title);
	echo $title;

	the_permalink();

	echo "\n";

	the_time(get_option( 'date_format' ));

	$this->the_content( __( '(more...)' ) );

	echo "\n";

endwhile;