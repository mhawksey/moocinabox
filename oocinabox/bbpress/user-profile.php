<?php

/**
 * User Profile
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

	<?php do_action( 'bbp_template_before_user_profile' ); ?>
	<?php if( $curauthmeta = get_user_meta( bbp_get_displayed_user_id() ) )
    		$curauthmeta = array_map( function( $a ){ return $a[0]; }, get_user_meta(bbp_get_displayed_user_id()));
			$curauth = get_userdata( bbp_get_displayed_user_id() ) ;
	?>
	<div id="bbp-user-profile" class="bbp-user bbp-user-profile">
		<h2 class="entry-title"><?php _e( 'Profile', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">
        	<div class="bbp-row bbp-user-name"><?php  printf( '<div class="bbp-field">Name:</div> <div class="bbp-value">%s %s</div>', $curauthmeta['first_name'], $curauthmeta['last_name']); ?></div>
            <div class="bbp-row bbp-user-display-name"><?php  printf( '<div class="bbp-field">Username:</div> <div class="bbp-value">%s</div>', $curauthmeta['nickname']); ?></div>
            <?php if(bbp_get_displayed_user_field( 'description' )!=""):?><div class="bbp-row bbp-user-description"><?php  printf( '<div class="bbp-field">Profile:</div> <div class="bbp-value"><em>%s</em></div>', bbp_get_displayed_user_field( 'description' )); ?></div><?php endif; ?>
         </div>
    </div>
    <div id="bbp-user-links" class="bbp-user bbp-user-links">
		<h2 class="entry-title"><?php _e( 'Links', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">   
		<div class="bbp-row bbp-user-website"><?php if ($url = $curauth->user_url)  printf( '<div class="bbp-field">Website:</div> <div class="bbp-value"><a href="%s" target="_blank" rel="nofollow">%s</a></div>', $url,$url); ?></div>
        <div class="bbp-row bbp-user-twitter"><?php if (preg_match('/[A-Za-z0-9_]{1,15}$/',$curauthmeta['twitter'], $screename))  printf( '<div class="bbp-field">Twitter:</div> <div class="bbp-value"><a href="http://twitter.com/%s" target="_blank" rel="nofollow">@%s</a></div>', $screename[0],$screename[0]); ?></div>
        <div class="bbp-row bbp-user-googleplus"><?php if ($url = $curauthmeta['googleplus'])  printf( '<div class="bbp-field">Google+:</div> <div class="bbp-value"><a href="%s" target="_blank" rel="nofollow">%s</a></div>', $url,$url); ?></div>
        <div class="bbp-row bbp-user-facebook"><?php if ($url = $curauthmeta['facebook'])  printf( '<div class="bbp-field">Facebook:</div> <div class="bbp-value"><a href="%s" target="_blank" rel="nofollow">%s</a></div>', $url,$url); ?></div>
        <div class="bbp-row bbp-user-facebook"><?php if ($url = $curauthmeta['blog'])  printf( '<div class="bbp-field">Blog:</div> <div class="bbp-value"><a href="%s" target="_blank" rel="nofollow">%s</a></div>', $url,$url); ?></div>
        <div class="bbp-row bbp-user-facebook"><?php if ($url = $curauthmeta['blogrss'])  printf( '<div class="bbp-field">Blog RSS:</div> <div class="bbp-value"><a href="%s" target="_blank" rel="nofollow">%s</a></div>', $url,$url); ?></div>
        </div>
    </div>
    <div id="bbp-user-forum" class="bbp-user bbp-user-forum">
		<h2 class="entry-title"><?php _e( 'Forum', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">
        	<div class="bbp-row bbp-user-forum-role"><?php  printf( '<div class="bbp-field">%s</div> <div class="bbp-value">%s</div>', __( 'Forum Role:',  'bbpress' ), bbp_get_user_display_role()); ?></div>
            <div class="bbp-row bbp-user-topic-count"><?php  printf( '<div class="bbp-field">%s</div> <div class="bbp-value">%s</div>', __( 'Topics Started:',  'bbpress' ), bbp_get_user_topic_count_raw()); ?></div>
            <div class="bbp-row bbp-user-reply-count"><?php  printf( '<div class="bbp-field">%s</div> <div class="bbp-value">%s</div>', __( 'Replies Created:', 'bbpress' ), bbp_get_user_reply_count_raw()); ?></div>
		</div>
	</div><!-- #bbp-author-topics-started -->
        <div id="bbp-user-forum" class="bbp-user bbp-user-content">
		<h2 class="entry-title"><?php _e( 'Aggregated Posts', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">
        	<div class="bbp-row bbp-user-post-count"><?php  printf( '<div class="bbp-field">%s</div> <div class="bbp-value"><a href="%s">%s</a></div>', __( 'Posts:',  'bbpress' ), get_author_posts_url(bbp_get_displayed_user_id()), count_user_posts( bbp_get_displayed_user_id() )); ?></div>
		</div>
	</div>

	<?php do_action( 'bbp_template_after_user_profile' ); ?>
