<?php
/**
 * Atom Feed Template for displaying Atom Posts feed.
 *
 * @package WordPress
 */

header('Content-Type: ' . feed_content_type('atom') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<feed
  xmlns="http://www.w3.org/2005/Atom"
  xmlns:thr="http://purl.org/syndication/thread/1.0"
  xml:lang="<?php bloginfo_rss( 'language' ); ?>"
  xml:base="<?php bloginfo_rss('url') ?>/wp-atom.php"
  <?php do_action('atom_ns'); ?>
 >
	<title type="text"><?php bloginfo_rss('name'); wp_title_rss(); ?> - Discussion Feed</title>
	<updated><?php echo mysql2date('Y-m-d\TH:i:s\Z', get_lastpostmodified('GMT'), false); ?></updated>

	<link rel="alternate" type="<?php bloginfo_rss('html_type'); ?>" href="<?php bloginfo_rss('url') ?>" />
	<id><?php bloginfo('atom_url'); ?></id>
	<link rel="self" type="application/atom+xml" href="<?php self_link(); ?>" />

	<?php do_action('atom_head'); ?>
<?php     
add_filter( 'post_limits', '__return_empty' );
function __return_empty(){	
}
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
?>   
    
    
	<?php while (have_posts()) : the_post(); ?>
	<entry>
		<author>
			<name><?php the_author() ?></name>
			<?php $author_url = get_the_author_meta('url'); if ( !empty($author_url) ) : ?>
			<uri><?php the_author_meta('url')?></uri>
			<?php endif;
			do_action('atom_author'); ?>
		</author>
		<title type="<?php html_type_rss(); ?>"><![CDATA[<?php the_title_rss() ?>]]></title>
		<link rel="alternate" type="<?php bloginfo_rss('html_type'); ?>" href="<?php bbp_topic_permalink(); ?>" />
		<id><?php the_guid() ; ?></id>
		<updated><?php echo get_post_modified_time('Y-m-d\TH:i:s\Z', true); ?></updated>
		<published><?php echo get_post_time('Y-m-d\TH:i:s\Z', true); ?></published>
		<?php the_category_rss('atom') ?>
		<summary type="<?php html_type_rss(); ?>"><![CDATA[<?php the_excerpt_rss(); ?>]]></summary>
<?php if ( !get_option('rss_use_excerpt') ) : ?>
		<content type="<?php html_type_rss(); ?>" xml:base="<?php the_permalink_rss() ?>"><![CDATA[<?php the_content_feed('atom') ?>]]></content>
<?php endif; ?>
<?php atom_enclosure(); ?>
<?php do_action('atom_entry'); ?>  
		<link rel="parent" type="<?php bloginfo_rss('html_type'); ?>" href="<?php echo get_permalink($post->post_parent) ?>"/>
	</entry>
	<?php endwhile ; ?>
</feed>