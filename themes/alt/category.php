<?php get_header(); ?>
<?php if (cat_is_ancestor_of(get_cat_id($root_cat), get_query_var('cat')) || is_category($root_cat)){
	$is_readerlite = true;
}
?>
	<div id="content">
		<div class="padder">

		<?php do_action( 'bp_before_archive' ); ?>

		<div class="page" id="blog-archives" role="main">
     <?php if ($is_readerlite || is_author() || is_day() || is_month() || is_year() ): ?>
     <?php if ( !is_user_logged_in() ) { 
             $log_reminder = '<strong>Note: Login to mark items as read, favourite and like posts</strong>';
			 $blog_reminder = 'login and register it in your profile';
 			} else {
			 $blog_reminder = '<a href="'.bp_core_get_user_domain(bp_loggedin_user_id()).'profile/edit/group/1/">register it in your profile</a>';
			}
			?>
			<h3 class="pagetitle">Course Reader</h3>
            
			<p>The Reader is searching and displaying content related to the course. If you have a blog <?php echo $blog_reminder; ?> and include the text #ocTEL in the title or post content for it to appear in the Reader (<a href="http://octel.alt.ac.uk/help/reader-help-and-information/">Help</a>). <?php echo $log_reminder; ?></p>
            <?php if (is_category('Newsletter Archive')):?>
             
            <?php if (!is_user_logged_in()){ ?>
            <?php global $bp; ?>
            To receive the Newsletter in you inbox <a href="<?php echo wp_login_url();?>" title="Login">login/register</a> to get manage your subscription (in My Profile &gt; Settings &gt; Notifications)
            <? } else {
            	get_mailpress_mlink(bp_core_get_user_email( $bp->loggedin_user->userdata->ID )); ?></p>
            <?php } ?>
            
            
            <?php endif; ?>
			<?php if ( have_posts() ) : ?>

				<?php bp_dtheme_content_nav( 'nav-above' ); ?>
					<div class="readerfeed" style="text-align:right"><a href="feed/" title="RSS Feed">RSS</a></div>
						<div id="accordionLoader" class="inifiniteLoader">Loading... </div>
              			<div id="accordion" style="display:none">
                          <?php require_once(sprintf("%s/templates/content-item.php", READER_C_PATH)); ?>
                        </div>
                    
                    <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

				<?php bp_dtheme_content_nav( 'nav-below' ); ?>

			<?php else : ?>

				<h2 class="center"><?php _e( 'Not Found', 'buddypress' ); ?></h2>
				<?php get_search_form(); ?>

			<?php endif; ?>
        <?php else : ?>
			<h3 class="pagetitle"><?php printf( __( 'You are browsing the archive for %1$s.', 'buddypress' ), wp_title( false, false ) ); ?></h3>

			<?php if ( have_posts() ) : ?>

				<?php bp_dtheme_content_nav( 'nav-above' ); ?>

				<?php while (have_posts()) : the_post(); ?>

					<?php do_action( 'bp_before_blog_post' ); ?>

					<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<div class="author-box">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
							<p><?php printf( _x( 'by %s', 'Post written by...', 'buddypress' ), bp_core_get_userlink( $post->post_author ) ); ?></p>
						</div>

						<div class="post-content">
							<h2 class="posttitle"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'buddypress' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

							<p class="date"><?php printf( __( '%1$s <span>in %2$s</span>', 'buddypress' ), get_the_date(), get_the_category_list( ', ' ) ); ?></p>

							<div class="entry">
								<?php the_content( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
								<?php wp_link_pages( array( 'before' => '<div class="page-link"><p>' . __( 'Pages: ', 'buddypress' ), 'after' => '</p></div>', 'next_or_number' => 'number' ) ); ?>
							</div>

							<p class="postmetadata"><?php the_tags( '<span class="tags">' . __( 'Tags: ', 'buddypress' ), ', ', '</span>' ); ?> <span class="comments"><?php comments_popup_link( __( 'No Comments &#187;', 'buddypress' ), __( '1 Comment &#187;', 'buddypress' ), __( '% Comments &#187;', 'buddypress' ) ); ?></span></p>
						</div>

					</div>

					<?php do_action( 'bp_after_blog_post' ); ?>

				<?php endwhile; ?>

				<?php bp_dtheme_content_nav( 'nav-below' ); ?>

			<?php else : ?>

				<h2 class="center"><?php _e( 'Not Found', 'buddypress' ); ?></h2>
				<?php get_search_form(); ?>

			<?php endif; ?>
          <?php endif; ?>
		</div>

		<?php do_action( 'bp_after_archive' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php get_sidebar('reader'); ?>

<?php get_footer(); ?>
