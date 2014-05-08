<?php $atts=array(
            'title'            => 'Latest Activity',//title of the section
            'pagination'       => 'false',//show or not
            'display_comments' => 'threaded',
            'include'          => false,     // pass an activity_id or string of IDs comma-separated
            'exclude'          => false,     // pass an activity_id or string of IDs comma-separated
            'in'               => false,     // comma-separated list or array of activity IDs among which to search
            'sort'             => 'DESC',    // sort DESC or ASC
            'page'             => 1,         // which page to load
            'per_page'         => 3,         //how many per page
            'max'              => false,     // max number to return

            // Scope - pre-built activity filters for a user (friends/groups/favorites/mentions)
            'scope'            => false,

            // Filtering
            'user_id'          => false,    // user_id to filter on
            'object'           => false,    // object to filter on e.g. groups, profile, status, friends
            'action'           => 'activity_update',    // action to filter on e.g. activity_update, new_forum_post, profile_updated
            'primary_id'       => false,    // object ID to filter on e.g. a group_id or forum_id or blog_id etc.
            'secondary_id'     => false,    // secondary object ID to filter on e.g. a post_id

            // Searching
            'search_terms'     => false         // specify terms to search on
        );
		?>
<?php if ( bp_has_activities($atts) ) : ?>
    <ul id="activity-list" <?php $this->classes('nopmb item-list'); ?>>
        <?php while ( bp_activities() ) : bp_the_activity(); ?>
            <li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">
                <div <?php $this->classes('nopmb item-avatar'); ?>>
                    <a href="<?php bp_activity_user_link(); ?>">
                        <?php bp_activity_avatar( 'type=full&width=30&height=30'); ?>
                    </a>
                </div>
                <div style="margin-left:40px">
                    <div>
                        <?php bp_activity_action(); ?>
                    </div>
                    <?php if ( 'activity_comment' == bp_get_activity_type() ) : ?>
                        <blockquote style="margin-left: 10px;">
                            <strong><?php _e( 'In reply to: ', 'buddypress' ); ?></strong><?php bp_activity_parent_content(); ?> <a href="<?php bp_activity_thread_permalink(); ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'buddypress' ); ?>"><?php _e( 'View', 'buddypress' ); ?></a>
                        </blockquote>
                    <?php endif; ?>
                    <?php if ( bp_activity_has_content() ) : ?>
                        <blockquote style="margin-left: 10px;">
                            <?php bp_activity_content_body(); ?>
                        </blockquote>
                    <?php endif; ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php endif; ?>
