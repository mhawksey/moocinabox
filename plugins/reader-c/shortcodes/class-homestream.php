<?php
/**
 * Shortcode to display survey data explorer
 *
 * Shortcode: [hypothesis_geosummary]
 * Options: post_id - hypothesis id (deafults to current post)
 *
 * Based on shortcode class construction used in Conferencer http://wordpress.org/plugins/conferencer/.
 *
 * @since 0.1.1
 *
 * @package Reader_C
 * @subpackage Reader_C_Shortcode
 */
 
new Reader_C_Shortcode_Homestream();
// Base class 'Evidence_Hub_Shortcode' defined in 'shortcodes/class-shortcode.php'.
class Reader_C_Shortcode_Homestream extends Reader_C_Shortcode {
	var $shortcode = 'homestream';
	public $defaults = array('title'            => 'Latest Activity',//title of the section
            'pagination'       => 'true',//show or not
            'display_comments' => 'threaded',
            'include'          => false,     // pass an activity_id or string of IDs comma-separated
            'exclude'          => false,     // pass an activity_id or string of IDs comma-separated
            'in'               => false,     // comma-separated list or array of activity IDs among which to search
            'sort'             => 'DESC',    // sort DESC or ASC
            'page'             => 1,         // which page to load
            'per_page'         => 5,         //how many per page
            'max'              => false,     // max number to return

            // Scope - pre-built activity filters for a user (friends/groups/favorites/mentions)
            'scope'            => false,

            // Filtering
            'user_id'          => false,    // user_id to filter on
            'object'           => false,    // object to filter on e.g. groups, profile, status, friends
            'action'           => false,    // action to filter on e.g. activity_update, new_forum_post, profile_updated
            'primary_id'       => false,    // object ID to filter on e.g. a group_id or forum_id or blog_id etc.
            'secondary_id'     => false,    // secondary object ID to filter on e.g. a post_id

            // Searching
            'search_terms'     => false,        // specify terms to search on
            'use_compat'       => true);
	
	
	/**
	* Generate post content. 
	*
	* @since 0.1.1
	* @return string.
	*/
	function content() {
		ob_start();
		extract($this->options);
		
?>
<div id="homestream">
<h3>Latest Activity <span style="font-size: 12px;">(<a href="<?php echo bp_get_activity_root_slug(); ?>">Read more/filter</a>)</span></h3>
 	<?php if( $use_compat):?>
        <div id="buddypress">
    <?php endif;?>		
	<?php if($title): ?>
            <h3 class="activity-shortcode-title"><?php echo $title; ?></h3>
        <?php endif;?>    
		
        <?php do_action( 'bp_before_activity_loop' ); ?>

        <?php if ( bp_has_activities($this->options)  ) : ?>
            <div class="activity <?php if(!$display_comments): ?> hide-activity-comments<?php endif; ?> shortcode-activity-stream">

                 <?php if ( empty( $_POST['page'] ) ) : ?>

                    <ul id="activity-stream" class="activity-list item-list">

                 <?php endif; ?>

                 <?php while ( bp_activities() ) : bp_the_activity(); ?>

                    <?php bp_get_template_part( 'activity/entry'); ?>

                 <?php endwhile; ?>

                 <?php if ( empty( $_POST['page'] ) ) : ?>
                    </ul>
                 <?php endif; ?>
                
                <?php if($pagination):?>
                    <div class="pagination">
                        <div class="pag-count"><?php bp_activity_pagination_count(); ?></div>
                        <div class="pagination-links"><?php bp_activity_pagination_links(); ?></div>
                    </div>
                <?php endif;?>
            </div>

	
	<?php else : ?>

        <div id="message" class="info">
            <p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'buddypress' ); ?></p>
        </div>
            
          
    <?php endif; ?>
            
    <?php do_action( 'bp_after_activity_loop' ); ?>

    <form action="" name="activity-loop-form" id="activity-loop-form" method="post">

        <?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ); ?>

    </form>
     <?php if( $use_compat ):?>       
        </div>
     <?php endif;?>
</div>	 	 
<div class="r" id="homestream"><a class="twitter-timeline" data-dnt="true" href="https://twitter.com/search?q=%23octel" data-link-color="#079948" data-widget-id="456389509450436608">Tweets about "#octel"</a>	 	 
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>	 	 
</div>
<?php
		return ob_get_clean();
	} // end of function content
	

} // end of class
