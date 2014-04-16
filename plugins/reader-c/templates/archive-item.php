<?php get_header(); ?>

<div id="content">
  <div class="padder">
    <?php do_action( 'bp_before_archive' ); ?>
    <div class="page" id="blog-archives" role="main">
      <h3 class="pagetitle"><?php printf( __( 'You are browsing the archive for %1$s.', 'buddypress' ), wp_title( false, false ) ); ?></h3>
      <?php if ( have_posts() ) : ?>
      <?php bp_dtheme_content_nav( 'nav-above' ); ?>
        <div class="readerfeed" style="text-align:right"><a href="feed/" title="RSS Feed">RSS</a></div>
        <div id="accordionLoader" class="inifiniteLoader">Loading... </div>
        <div id="accordion" style="display:none">
      	<?php get_template_part( 'content', 'item' ); ?>
        </div>
    <?php //bp_dtheme_content_nav( 'nav-below' ); ?>
    <?php else : ?>
    <h2 class="center">
      <?php _e( 'Not Found', 'buddypress' ); ?>
    </h2>
   
       
    <?php get_search_form(); ?>
    <?php endif; ?>
  </div>
  <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
  <?php do_action( 'bp_after_archive' ); ?>
</div>
<!-- .padder -->
</div>
<!-- #content -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
