<?php
header('Content-Type: text/plain; charset=' . get_option('blog_charset'), true);
$more = 1;
// Create a new filtering function that will add our where clause to the query
function filter_where( $where = '' ) {
	// posts in the last 7 days
	$where .= " AND post_date > '" . date('Y-m-d', strtotime('-7 days')) . "'";
	return $where;
}
function __return_empty(){	
}
add_filter( 'posts_where', 'filter_where' );
add_filter( 'post_limits', '__return_empty' );
$the_query = array(
							'author'         => 0,
							'feed'           => true,
							'post_type'      => array( bbp_get_reply_post_type(), bbp_get_topic_post_type() ),
							'post_parent'    => 'any',
							'post_status'    => join( ',', array( bbp_get_public_status_id(), bbp_get_closed_status_id() ) ),
							'posts_per_page' => -1,
							'order'          => 'DESC',
							'meta_query'     => $meta_query
						);
query_posts( $the_query );
remove_filter( 'posts_where', 'filter_where' );

rewind_posts(); while (have_posts()): the_post(); 
$text = get_the_content();
$text = preg_replace('/</',' <',$text); 
$text = preg_replace('/>/','> ',$text); 
echo html_entity_decode(preg_replace( "/\r|\n/", "", strip_tags($text) ),ENT_QUOTES,'UTF-8')."\n\n";

//echo ;
//echo mysql2date('Y-m-d\TH:i:s\Z', $post->post_date_gmt, false)."\n\n";
endwhile; 
?>