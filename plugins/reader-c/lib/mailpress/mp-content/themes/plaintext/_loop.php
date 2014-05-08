<?php

while (have_posts()) : the_post();

	echo "\n";

	$title = $this->get_the_title(); 
	$title = trim($title);
	$box   = str_repeat( '~', strlen(utf8_decode($title)) );
	echo "* $box *\n! $title !\n* $box *\n";

	the_permalink();

	echo "\n";

	the_time(get_option( 'date_format' ));

	$this->the_content( __( '(more...)' ) );

	echo "\n";

endwhile;