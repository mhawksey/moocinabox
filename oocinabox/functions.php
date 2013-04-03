<?php
/**
 * Add theme support for infinity scroll
 */
global $root_cat; 
$root_cat = "reader"; 
global $ajaxedload;
$ajaxedload = false;

//require_once(ABSPATH . 'wp-content/plugins/bbpress/includes/common/template-tags.php');

add_action( 'init', 'infinite_scroll_init' );
//add_action( 'wp_footer', 'infiniteFooter' );
add_action('wp_ajax_ajaxify', 'ajaxify');           // for logged in user  
add_action('wp_ajax_nopriv_ajaxify', 'ajaxify'); 
add_action('wp_ajax_ajaxFeedSearch', 'ajaxFeedSearch');  
//add_action('wp_ajax_nopriv_ajaxify', 'ajaxFeedSearch');
//add_action( 'wp_head', 'infintieHeader' );
add_action('wp_enqueue_scripts', 'my_scripts_method');
add_filter('user_contactmethods','add_hide_profile_fields',10,1);
add_action('profile_update', 'update_extra_profile_fields');
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
add_filter( 'the_excerpt', 'custom_excerpt' );
function custom_excerpt_length( $length ) {
	return 20;
}
function custom_excerpt($text){
   $text = strip_tags( $text );
   $tags = array("<p>", "</p>", "<br>", "<br />");
   $text = str_replace($tags, " ", $text);
   return $text;
}
 
function update_extra_profile_fields($user_id) {
	$linkid = get_user_meta($user_id, 'link_id', true);
	//$print_r($linkid);
	$blogurl = $_POST['blog'];
	$blogrss = $_POST['blogrss'];
	if ($blogrss != "" && $blogurl == "") $blogurl = $blogrss;
	if ($blogrss != "" && $blogurl != ""){ 
		$newid = make_link($user_id, $blogurl, $blogrss, $linkid);
		update_user_meta($user_id, 'link_id', $newid);
	}
}

function make_link($user_id, $blogurl, $blogrss, $linkid = false) {
	// a lot of this was inspired by http://wrapping.marthaburtis.net/2012/08/22/perfecting-the-syndicated-blog-sign-up/
	remove_filter('pre_link_rss', 'wp_filter_kses');
	remove_filter('pre_link_url', 'wp_filter_kses');
	// Get contributors category 
	$mylinks_categories = get_terms('link_category', 'name__like=Contributors');
	$contrib_cat = intval($mylinks_categories[0]->term_id);
	
	$link_notes = 'map authors: name\n*\n'.$user_id;
	$new_link = array(
			'link_name' => $blogurl,
			'link_url' => $blogurl,
			'link_category' => $contrib_cat,
			'link_rss' => $blogrss
			);
	if( !function_exists( 'wp_insert_link' ) )
		include_once( ABSPATH . '/wp-admin/includes/bookmark.php' );	
			
	if (!($linkid)) { // if no link insert new link
		$linkid = wp_insert_link($new_link);
		// update new link with notes
		global $wpdb;
		$esc_link_notes = $wpdb->escape($link_notes);
		$result = $wpdb->query("
			UPDATE $wpdb->links
			SET link_notes = \"".$esc_link_notes."\" 
			WHERE link_id='$linkid'
		");
	} else {
		//update existing link
		$new_link['link_id'] = $linkid;
		$linkid = wp_insert_link($new_link);
	}
	return $linkid;
}

function redirect_to_profile($redirect_to, $request, $user ) {
	if (function_exists('bbp_get_user_profile_url')) {
		$redirect_to = bbp_get_user_profile_url($user->ID);
		return $redirect_to;
	}
}
add_filter('login_redirect', 'redirect_to_profile', 10, 3);

function mf_remove_menu_pages() {
	if(!current_user_can('delete_published_posts')) {
		remove_menu_page( 'index.php' );
		remove_menu_page( 'jetpack' );
	}
}
add_action('jetpack_admin_menu', 'mf_remove_menu_pages');

// http://wordpress.org/support/topic/some-very-useful-tips-for-mailpress
function get_mailpress_mlink($user_email) {
  global $wpdb;
  $mailpress_user_key = $wpdb->get_var($wpdb->prepare("SELECT confkey FROM wp_mailpress_users WHERE email = '$user_email';"));
  	echo '<ul><li><a href="/newsletter-subscription/?action=mail_link&del=' . $mailpress_user_key . '" target="_blank">Manage Newsletter Subscriptions</a></li></ul>';
}

function add_hide_profile_fields( $contactmethods ) {
	unset($contactmethods['aim']);
	unset($contactmethods['jabber']);
	unset($contactmethods['yim']);
	$contactmethods['twitter'] = 'Twitter';
	$contactmethods['facebook'] = 'Facebook';
	$contactmethods['googleplus'] = 'Google+';
	$contactmethods['blog'] = 'Blog';
	$contactmethods['blogrss'] = 'Blog RSS Feed';
return $contactmethods;
}

register_sidebar(array(
	'name' => __('Archive Sidebar', 'responsive'),
	'description' => __('Area 2 - sidebar-archive.php', 'responsive'),
	'id' => 'archive',
	'before_title' => '<div class="widget-title">',
	'after_title' => '</div>',
	'before_widget' => '<div id="%1$s" class="widget-wrapper %2$s">',
	'after_widget' => '</div>'
));
register_sidebar(array(
	'name' => __('Forum Sidebar', 'responsive'),
	'description' => __('Area 2 - sidebar-bbpress.php', 'responsive'),
	'id' => 'bbpress',
	'before_title' => '<div class="widget-title">',
	'after_title' => '</div>',
	'before_widget' => '<div id="%1$s" class="widget-wrapper %2$s">',
	'after_widget' => '</div>'
));
 
function infinite_scroll_init() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'accordion',
		'render'    => 'infinite_scroll_render',	
		'wrapper'   => false,
		'footer'    => false
	) );
}

/**
 * Set the code to be rendered on for calling posts,
 * hooked to template parts when possible.
 *
 * Note: must define a loop.
 */
function infinite_scroll_render() {
	if(!$ajaxedload){
		get_template_part( 'content' );
	} else {
		get_template_part( 'content-ajaxed' );
	}
}


function my_scripts_method() {
if ( !is_admin() ) {
	wp_enqueue_script('jquery-ui-accordion'); 
    }
}
// record if post has been read in the reader view
include("include/readerlite_mark_post_as_read.php");
if (get_option('readerlite_mark_as_read') != 'true'){
	include("include/installation.php");
	readerlite_mar_install();
	add_option('readerlite_mark_as_read','true');
}

function ajaxFeedSearch() {
  	$url = $_POST['blog'];
	$output = '<select name="blogrss" id="blogrss" class="regular-text" tabindex="110" style="width:229px">';
	// stolen from Alan Levine (@cogdog)
    if($html = @DOMDocument::loadHTML(file_get_contents($url))) {
  
        $xpath = new DOMXPath($html);
        $options = false;
         
        // find RSS 2.0 feeds
        $feeds = $xpath->query("//head/link[@href][@type='application/rss+xml']/@href");
        foreach($feeds as $feed) {
            //$results[] = $feed->nodeValue;
			$urlStr = $feed->nodeValue;
			$parsed = parse_url($urlStr);
			if (empty($parsed['scheme'])) $urlStr = untrailingslashit($url).$urlStr;
			$options .= '<option value="'.$urlStr.'">'.$urlStr.'</option>';
        }
  
         // find Atom feeds
        $feeds = $xpath->query("//head/link[@href][@type='application/atom+xml']/@href");
        foreach($feeds as $feed) {
            //$results[] = $feed->nodeValue;
			$urlStr = $feed->nodeValue;
			$parsed = parse_url($urlStr);
			if (empty($parsed['scheme'])) $urlStr = untrailingslashit($url).$urlStr;
			$options .= '<option value="'.$urlStr.'">'.$urlStr.'</option>';
        }
		
        //$single_rss_url = $results[0];
        //return $single_rss_url;
    }
	
	$options .= '<option value="Other">Other</option>';
	$output .= $options.'</select>';
	$output .= '<input id="other_feed" name="other_feed" type="text" placeholder="Enter other feed" />';
	
    echo $output;
	die(1);;
}



function ajaxify() { // loads post content into accordion
    $post_id = $_POST['post_id'];
	$post_type = $_POST['post_type'];
	query_posts(array('p' => $post_id, 'post_type' => array('post')));
	ob_start();
	while (have_posts()) : the_post(); 
	if ($post_type == "summary") {
		$source = html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8');
		$posturl = get_permalink($post_id);
		$posturlen = urlencode($posturl);
		$title = html_entity_decode(get_the_title($post_id),ENT_QUOTES,'UTF-8');
		$titleen = rawurlencode($title);
		$buttons = '<div class="share_widget post-'.$post_id.'">Share: ' 
			. '<div class="gplusbut"><g:plusone size="small" annotation="none" href="'.$posturl.'"></g:plusone><script type="text/javascript">  window.___gcfg = {lang: "en-GB"};  (function() { var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;    po.src = "https://apis.google.com/js/plusone.js";    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);  })();</script></div><span class="share-count"><i></i><u></u><span id="gp-count">-</span></span>'
			. ' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.facebook.com/sharer.php?u='.$posturlen.'\')" >Facebook</a><span class="share-count"><i></i><u></u><span id="fb-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://twitter.com/intent/tweet?text='.$titleen.'%20'.$posturlen.'\')">Twitter</a><span class="share-count"><i></i><u></u><span id="tw-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.linkedin.com/shareArticle?mini=true&url='.$posturlen.'&source=MASHe&summary='.$titleen.'\')" >LinkedIn</a><span class="share-count"><i></i><u></u><span id="li-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://delicious.com/post?v=4&url='.$posturlen.'\')" >Delicious</a><span class="share-count"><i></i><u></u><span id="del-count">--</span></span>';
		 ?>
		   <div class="loaded-post">
			<div class="post-meta">
			<?php responsive_post_meta_data(); ?> from <a href="<?php the_syndication_source_link(); ?>" target="_blank"><?php print $source; ?></a><br/>
			<?php the_tags(__('Tagged with:', 'responsive') . ' ', ', ', '<br />'); ?> 
			<?php printf(__('Posted in %s', 'responsive'), get_the_category_list(', ')); ?>
			<!-- end of .post-data -->  
		   </div>
		   <div class="post-entry">
			 <?php the_content(__('Read more &#8250;', 'responsive')); ?>
		   </div>
		  </div>
		  <div sytle="clear:both"></div>
		   <?php echo $buttons; ?>
		   <?php
		
	} 
	if(function_exists('readerlite_mark_post_as_read')){
    	readerlite_mark_post_as_read();
	}
	endwhile;
	$output = ob_get_contents();
	ob_end_clean();
	echo $output;
	die(1);
}
?>