<?php 
	global $wp_query;
	$args = array_merge( $wp_query->query_vars, array( 'category_name' => 'reader' ) );
	$args['posts_per_page'] = -1;
	
	$my_query = new WP_Query($args);
	while ($my_query->have_posts()) : $my_query->the_post();
	 $cats = get_the_category(); 
	 foreach($cats as $c) {
		 //print_r($c);
		 if ($c->category_parent > 0) {
			$catcount[$c->cat_name] +=1;
			$catreplace[$c->cat_name.'</a>'] = $c->cat_name. '</a> (<strong>'. $catcount[$c->cat_name].'</strong>)';
		 }
	 }
	endwhile; 
	wp_reset_postdata();
	$catlist = wp_list_categories('echo=0&show_count=0&title_li=&exclude=1');
	$catlist = strtr($catlist, $catreplace);
	$catlist = preg_replace('/<li[^>]*>/','<li style="margin:0">',$catlist);
	$catlist = preg_replace('/<ul[^>]*>/','<ul style="padding-left:20px;">',$catlist);
	echo $catlist;