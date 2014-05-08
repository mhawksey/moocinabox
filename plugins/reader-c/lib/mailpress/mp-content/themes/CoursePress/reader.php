<?php
/*
Template Name: reader
*/

$this->get_header();
while (have_posts()) : the_post(); 
?>

<div><h2><a href="<?php the_permalink(); ?>" ><?php $this->the_title(); ?></a></h2></div>
			
<div><?php $this->the_content(  sprintf( 'Continue reading %s', '<span class="meta-nav">&rarr;</span>') ); ?></div>

<!-- #post-<?php the_ID(); ?> -->

<?php 
endwhile;
$this->get_footer();
