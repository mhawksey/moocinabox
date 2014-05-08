<?php 
	global $wp_query;
	$args = array_merge( $wp_query->query_vars, array( 'category_name' => 'course-information', 'posts_per_page' => -1 ) );
	
	$my_query = new WP_Query($args);
	if ($my_query-> have_posts() ) :
		while ($my_query->have_posts()) : $my_query->the_post();
		?>
        <div <?php MP_mail_::classes('cp_cdiv'); ?>>
        	<h2 <?php $this->classes('cp_ch2'); ?>> <a <?php $this->classes('cp_clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
         	 <?php the_title(); ?></a></h2>
        	<small <?php $this->classes('nopmb cdate'); ?>><?php the_time('F j, Y') ?></small>
        	<div <?php $this->classes('nopmb cp_div'); ?>><?php $this->the_content( __( 'Read more >>' ) ); ?></div>
      	</div>
      <?php
		endwhile; 
	else: ?>
		<div <?php $this->classes('cp_cdiv'); ?>>
        	<div <?php $this->classes('nopmb'); ?>>
          		<p <?php $this->classes('nopmb noinfo'); ?>>No new news in this newsletter</p>
        	</div>
      	</div>
    <?php
	endif;
	wp_reset_postdata();
