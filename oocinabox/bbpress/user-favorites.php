<?php

/**
 * User Favorites
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

	<?php do_action( 'bbp_template_before_user_favorites' ); ?>

	<div id="bbp-user-favorites" class="bbp-user-favorites">
		<h2 class="entry-title"><?php _e( 'Favorite Forum Topics', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">

			<?php if ( bbp_get_user_favorites() ) : ?>

				<?php bbp_get_template_part( 'pagination', 'topics' ); ?>

				<?php bbp_get_template_part( 'loop',       'topics' ); ?>

				<?php bbp_get_template_part( 'pagination', 'topics' ); ?>

			<?php else : ?>

				<p><?php bbp_is_user_home() ? _e( 'You currently have no favorite topics.', 'bbpress' ) : _e( 'This user has no favorite topics.', 'bbpress' ); ?></p>

			<?php endif; ?>

		</div>
        <h2 class="entry-title">Favorite Posts</h2>
		<div class="wp-favourite-user-section">
			<?php 
				  $favorite_post_ids = get_user_meta(bbp_get_displayed_user_id(), 'wpfp_favorites');
				  $favorite_post_ids = $favorite_post_ids[0];
				  include("wpfp-page-template.php"); ?>
		</div>
	</div><!-- #bbp-user-favorites -->

	<?php do_action( 'bbp_template_after_user_favorites' ); ?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/readerlite.js"></script>