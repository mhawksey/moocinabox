<?php 
$root_cat = "Reader";
require ( get_stylesheet_directory() . '/includes/XProfile_FWP.php' );
add_filter('badgeos_public_submissions', 'set_public_badge_submission', 999, 1);

function set_public_badge_submission($public){
	$public = true;	
	return $public;
}
function create_forum_activity_feed() {
    load_template( get_stylesheet_directory() . '/customfeedforumactivity.php'); 
	
}
if (function_exists('bbp_get_reply_post_type')){
	add_action('do_feed_forum-activity', 'create_forum_activity_feed', 10, 1);
}

/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 *
 * @return void
 */
function alt_customize_register( $wp_customize ) {
	/**
	 * Add custom header logo by Kirk Wight.
	 *
	 * @link http://kwight.ca/2012/12/02/adding-a-logo-uploader-to-your-wordpress-site-with-the-theme-customizer/
	 * 
	 * Create a new section for our logo upload.
	 */
	$wp_customize->add_section( 'alt_logo_section' , array(
    'title'       => __( 'Logo', 'alt' ),
    'priority'    => 30,
    'description' => 'Upload a logo to replace the default site name and description in the header',
	) );
	
	// Register our new setting.
	$wp_customize->add_setting( 'alt_logo' );
	
	// Tell the Theme Customizer to let us use an image uploader for setting our logo.
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'alt_logo', array(
    'label'    => __( 'Logo', 'alt' ),
    'section'  => 'alt_logo_section',
    'settings' => 'alt_logo',
	) ) );
}
add_action( 'customize_register', 'alt_customize_register' );

function lost_password_link(){	
	echo ('<p align="right"><a href="'.wp_lostpassword_url().'" title="Lost Password">Lost Password?</a></p>');	
	//echo ("<p>Online delegates can <a href=\"/register\" title=\"Create an account\">request an account</a> (if you've registered to attend the conference and have not received login details please use the <a href=\"javascript:void(0)\" onclick=\"usernoise.window.show()\">Feedback</a> form).</p>");
}
add_filter('bp_after_sidebar_login_form', 'lost_password_link');

if (function_exists('sc_render_login_form_social_connect')){
	function do_socialconnect(){
		return do_action('social_connect_form');
	}
	add_filter('bp_sidebar_login_form', 'do_socialconnect');
}

/**
 * Redirect back to homepage and not allow access to 
 * WP admin for Subscribers.
 */
function themeblvd_redirect_admin(){
	if ( ! current_user_can( 'edit_posts' ) && is_admin() && $_SERVER['PHP_SELF'] != '/wp-admin/admin-ajax.php' ){
		wp_redirect( bp_core_get_user_domain(bp_loggedin_user_id()) );
		exit;		
	}
}
add_action( 'admin_init', 'themeblvd_redirect_admin' );

function alt_widgets_init() {
	register_sidebar( array(
			'name' => 'Custom Header Widget Area',
			'id' => 'header-area',
			'description' => 'Header widget area',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );
	register_sidebar( array(
			'name' => 'Custom Footer Widget Area',
			'id' => 'footer-area',
			'description' => 'Footer Widget Area',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) );
}
add_action( 'widgets_init', 'alt_widgets_init' );

function addFooterWidget(){
?>
<div id="footer-widget-area">
   <?php dynamic_sidebar( 'Custom Footer Widget Area' ); ?>
</div>     
<?
}
add_filter('bp_footer', 'addFooterWidget');

function addHeaderWidget(){
?>
<div id="header-widget-area">
   <?php dynamic_sidebar( 'Custom Header Widget Area' ); ?>
</div>     
<?
}
add_filter('bp_before_header', 'addHeaderWidget');

function edit_profile_link($atts){
		extract( shortcode_atts( array(
	      'text' => 'edit your profile',
		  'link' => '/login/',
		  'link_post' => ''
     ), $atts ) );

	if ( is_user_logged_in() ) {
		$link = bp_core_get_user_domain(bp_loggedin_user_id()) . $link_post ;
	}
	$output = '<a href="'.$link.'">'.$text.'</a>';
	return $output;
}
add_shortcode('edit_profile_link', 'edit_profile_link');

function newsletter_subscription_notification_settings() {
	global $bp ;?>
	<table class="notification-settings zebra" id="groups-notification-settings">
	<thead>
		<tr>
			<th class="icon"></th>
			<th class="title">Newsletter Subscription</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td></td>
			<td><?php get_mailpress_mlink(bp_core_get_user_email( $bp->loggedin_user->userdata->ID )); ?></td>
		</tr>
	</tbody>
	</table>	
<?php
}
if (class_exists('MailPress') && function_exists('get_mailpress_mlink')){
	add_action( 'bp_notification_settings', 'newsletter_subscription_notification_settings' );
}

// http://wordpress.org/support/topic/some-very-useful-tips-for-mailpress
function get_mailpress_mlink($user_email) {
  	echo 'To manage your newsletter subscription goto <a href="'.MP_User::get_unsubscribe_url(MP_User::get_key_by_email($user_email)).'">Manage Newsletter Subscriptions</a>';
}
/**
 * Checks if a particular user has a role. 
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return bool
 */
function appthemes_check_user_role( $role, $user_id = null ) {
 
    if ( is_numeric( $user_id ) )
	$user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();
 
    if ( empty( $user ) )
	return false;
 
    return in_array( $role, (array) $user->roles );
}

function get_user_blogs($key){
	global $wpdb;
	$blog_id = get_current_blog_id();
	$users_id = $wpdb->get_col( $wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key='%s' AND meta_value <>  ''", $key ));
	foreach ( $users_id as $user_id ) :
	  if (is_user_member_of_blog($user_id, $blog_id)):
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

/**
 * Nav Menu Dropdown
 *
 * @package      BE_Genesis_Child
 * @since        1.0.0
 * @link         https://github.com/billerickson/BE-Genesis-Child
 * @author       Bill Erickson <bill@billerickson.net>
 * @copyright    Copyright (c) 2011, Bill Erickson
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */
 
class Walker_Nav_Menu_Dropdown extends Walker_Nav_Menu {
	function start_lvl(&$output, $depth = 0, $args = array()){
		$indent = str_repeat("\t", $depth); // don't output children opening tag (`<ul>`)
	}
 
	function end_lvl(&$output, $depth = 0, $args = array()){
		$indent = str_repeat("\t", $depth); // don't output children closing tag
	}
 
	/**
	* Start the element output.
	*
	* @param  string $output Passed by reference. Used to append additional content.
	* @param  object $item   Menu item data object.
	* @param  int $depth     Depth of menu item. May be used for padding.
	* @param  array $args    Additional strings.
	* @return void
	*/
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0 ) {
 		$url = '#' !== $item->url ? $item->url : '';
 		$output .= '<option value="' . $url . '">' . str_repeat("—",$depth) .' '. $item->title;
	}	
 
	function end_el(&$output, $item, $depth = 0, $args = array()){
		$output .= "</option>\n"; // replace closing </li> with the option tag
	}
}