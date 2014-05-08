<?php

/*

Template Name: MailPress

*/

?>

<?php if (class_exists('MailPress')) $results = MP_Mail_links::process(); ?>
<?php get_header(); ?>

	<div id="content">
		<div class="padder">

			<?php do_action( 'bp_before_blog_single_post' ); ?>

			<div class="page" id="blog-single" role="main">


				<div id="post-" >

					<div class="post-content">
						<h2 class="posttitle"><?php echo $results ['title']; ?></h2>

						
						<div class="entry">
							<?php echo $results ['content']; ?>

						</div>
					</div>

				</div>

		</div>

		<?php do_action( 'bp_after_blog_single_post' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php get_sidebar(); ?>

<?php get_footer(); ?>

