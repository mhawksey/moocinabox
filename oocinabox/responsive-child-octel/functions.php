<?php
/**
 * Add theme support for infinity scroll
 */
global $root_cat; 
$root_cat = "Course Reader"; 
global $ajaxedload;
$ajaxedload = false;

// custom redirect on user submitted posts		
add_filter('the_permalink','my_permalink_redirect');
add_filter('post_link', 'my_permalink_redirect', /*priority=*/ 1);
function my_permalink_redirect($permalink) {
    global $post;
    if ( get_post_meta($post->ID, 'user_submit_url', true)) {
		return get_post_meta($post->ID, 'user_submit_url', true); 
    } else {
		return $permalink;
	}
}

if (function_exists('user_submitted_posts')) 
	add_action( 'added_post_meta', 'add_course_reader_to_other', 10, 4 );
// http://wordpress.stackexchange.com/a/16840
function add_course_reader_to_other( $meta_id, $post_id, $meta_key, $meta_value ){
    if ( 'is_submission' == $meta_key ) {
        wpse16835_do_something( $post_id );
    }
}

function wpse16835_do_something( $post_id ){
	$category = wp_get_post_categories($post_id); 
	$newcat    = get_category_by_slug('reader');
	array_push( $category, $newcat->term_id );
	wp_set_post_categories( $post_id, $category );
	add_post_meta($post_id, 'syndication_source', 'submission form', true);
	add_post_meta($post_id, 'syndication_source_uri', '#', true);
	add_post_meta($post_id, 'testi', $category, true);
}
				
add_action( 'init', 'infinite_scroll_init' );
add_action('wp_ajax_ajaxify', 'ajaxify');           // for logged in user  
add_action('wp_ajax_nopriv_ajaxify', 'ajaxify'); 
add_action('wp_ajax_ajaxFeedSearch', 'ajaxFeedSearch');  
add_action('wp_enqueue_scripts', 'my_scripts_method');
add_filter('user_contactmethods','add_hide_profile_fields',10,1);
add_action('profile_update', 'update_extra_profile_fields');
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
add_filter( 'the_excerpt', 'custom_excerpt' );
function custom_excerpt_length( $length ) {
	return 20;
}
function is_subscriber($userid){
	$user = new WP_User( $userid );
	$is_sub = false;
    if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
        foreach ( $user->roles as $role ){
            if ($role=="subscriber") 
                $is_sub = true;
				return $is_sub;
		}
    }
	return $is_sub;
}
	
function get_user_blogs($key){
	global $wpdb;
	$users_id = $wpdb->get_col( $wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key='%s' AND meta_value <>  ''", $key ));
	foreach ( $users_id as $user_id ) :
	  if (is_subscriber($user_id)):
		$usermeta = array_map( function( $a ){ return $a[0]; }, get_user_meta($user_id));
		$user = get_userdata( $user_id );
		$tmp = array();
		$tmp['id'] = $user_id;
		$tmp['user_login'] = $user->user_login;
		$tmp['first_name'] = $user->first_name;
		$tmp['last_name'] = $user->last_name;
		$tmp['blog'] = $usermeta['blog'];
		$tmp['blogrss'] = $usermeta['blogrss'];
		$result[] = $tmp;
	  endif;
	endforeach; // end the users loop.
	if ($result) {
		usort($result, function($a, $b) {
			return strcmp($a['last_name'], $b['last_name']);
		});
	}
	return $result;
}
function display_user_blogs( $atts ) {
	 $blogs = get_user_blogs ('blog');
	 $output = "";	
	 if ($blogs):
		 $output .= '<ul class="blogs">';
		 foreach ($blogs as $user){
			 if (isValidURL($user['blog'])){
				 $rssfeed = "";
				 if ($user['blogrss']!=="" && isValidURL($user['blogrss']))
					$rssfeed = '[<a href="'.$user['blogrss'].'" title="RSS for '.$user['blog'].' target="_blank">RSS Feed</a>]';
				 $output .=  '<li><a href="/'.bbp_get_user_slug().'/'.$user['user_login'].'">'.ucwords( strtolower( $user['first_name'] . ' ' . $user['last_name'] ) ).'</a> - <a href="'.$user['blog'].'" target="_blank">'.$user['blog'].'</a> '.$rssfeed.'</li>';
			 }
		 }
		 $output .=  '</ul>';
	 endif;
	 return $output;
}
function isValidURL($url) {
    if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) return true;
    else return false;
}
add_shortcode('display_user_blogs', 'display_user_blogs');

function display_top_authors( $atts ) {
	extract( shortcode_atts( array(
	      'limit' => '10',
		  'type' => 'post'
     ), $atts ) );
	// http://wordpress.org/support/topic/how-to-show-top-authors-with-avatar-and-count#post-1408011
	global $wpdb;
	$output = "";
	$top_authors = $wpdb->get_results("
		SELECT u.ID, count(post_author) as posts FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->users} as u ON p.post_author = u.ID
		INNER JOIN $wpdb->usermeta m ON m.user_id = u.ID
		WHERE p.post_status = 'publish'
		AND p.post_type = '{$type}'
		AND m.meta_key = 'wp_capabilities'
		AND m.meta_value LIKE '%subscriber%'
		GROUP by p.post_author
		ORDER by posts DESC
		LIMIT 0,{$limit}
	");
	if( !empty( $top_authors ) ) {
		$output .= '<ul>';
		foreach( $top_authors as $key => $author ) {
			if ($type == 'reply'){
				$url = bbp_get_user_profile_url( $author->ID ).'replies/';
			} else {
				$url = get_author_posts_url( $author->ID );
			}
			$output .= '
			<li>
				' . get_avatar( $author->ID , 16 ) . ' <a href="' . $url . '">' . get_the_author_meta( 'user_nicename' , $author->ID ) . '</a>
				(' . $author->posts . ') 
			</li>
			';
		}
		$output .= '</ul>';
	}
	return $output;
}

add_shortcode('display_top_authors', 'display_top_authors');

function edit_profile_link($atts){
		extract( shortcode_atts( array(
	      'text' => 'edit your profile',
		  'link' => '/login/'
     ), $atts ) );
	
	if ( is_user_logged_in() ) {
		global $current_user;
      	get_currentuserinfo();
		$link = "/forums/users/".$current_user->user_login."/edit";
	}
	$output = '<a href="'.$link.'">'.$text.'</a>';
	return $output;
}
add_shortcode('edit_profile_link', 'edit_profile_link');

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
	$onlist = $_POST['_show_on_list'];
	$current_role = $_POST['_current_subscriber_role'];
	if ($onlist != $current_role && ($current_role == "subscriber" || $current_role=="subscriber-unlisted")){
		// http://wordpress.stackexchange.com/a/4727
		$u = new WP_User( $user_id );
		// Remove role
		$u->remove_role( $current_role );
		// Add role
		if ($onlist == "subscriber"){
			$u->add_role( 'subscriber' );
		} else {
			$u->add_role( 'subscriber-unlisted' );
		}
	}
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
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://plus.google.com/share?url='.$posturlen.'\')" ><img src="https://www.gstatic.com/images/icons/gplus-16.png" alt="Share on Google+"/></a><span class="share-count"><i></i><u></u><span id="gp-count">--</span></span>'
            .' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.facebook.com/sharer.php?u='.$posturlen.'\')" >Facebook</a><span class="share-count"><i></i><u></u><span id="fb-count">--</span></span>'
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


//load feed template
function create_txt_feed() {
    load_template( get_stylesheet_directory() . '/customfeed.php'); 
	
}
add_action('do_feed_txt', 'create_txt_feed', 10, 1);
function create_txt_forum_feed() {
    load_template( get_stylesheet_directory() . '/customfeedforum.php'); 
	
}
add_action('do_feed_txt-forum', 'create_txt_forum_feed', 10, 1);
function create_forum_activity_feed() {
    load_template( get_stylesheet_directory() . '/customfeedforumactivity.php'); 
	
}
add_action('do_feed_forum-activity', 'create_forum_activity_feed', 10, 1);

function wpfp_list_most_favorited_with_star($limit=5) {
    global $wpdb;

    $query = "SELECT post_id, meta_value, post_status FROM $wpdb->postmeta";
    $query .= " LEFT JOIN $wpdb->posts ON post_id=$wpdb->posts.ID";
    $query .= " WHERE post_status='publish' AND meta_key='".WPFP_META_KEY."' AND meta_value > 0 ORDER BY ROUND(meta_value) DESC LIMIT 0, $limit";
    $results = $wpdb->get_results($query);
    if ($results) {
        echo "<ul class='wpf_custom_widget'>";
        foreach ($results as $o):
            $p = get_post($o->post_id);
            echo "<li>".wpfp_show_star($o->post_id)."<div class='wpf_custom_div'>";
            echo " <a href='".get_permalink($o->post_id)."' title='". $p->post_title ."'>" . $p->post_title . "</a> ($o->meta_value) ";
            echo "</div></li>";
        endforeach;
        echo "</ul>";
    }
}

function wpfp_show_star($post_id) {
	$str = "<div class='wpfp-span wpf_custom_star'>";
	$str .= wpfp_link(1, "", 0, array( 'post_id' => $post_id ) );
	$str .= "</div>";
	return $str;
}


?>