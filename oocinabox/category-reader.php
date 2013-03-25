<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Archive Template
 *
 *
 * @file           category-reader.php
 * @package        Responsive 
 * @author         Martin Hawksey 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.1
 * @filesource     wp-content/themes/responsive/archive.php
 * @link           http://codex.wordpress.org/Theme_Development#Archive_.28archive.php.29
 * @since          available since Release 1.0
 */
?>
<?php get_header(); ?>
        <div id="content-archive" class="grid col-620">

<?php if (have_posts()) : ?>
<?php
ob_start();
responsive_breadcrumb_lists();
$breadcrumb = ob_get_contents();
ob_end_clean();
$breadcrumb = str_replace("(Page 1)","",$breadcrumb);
?>
        
        <?php $options = get_option('responsive_theme_options'); ?>
		<?php if ($options['breadcrumb'] == 0): ?>
		<?php echo $breadcrumb; ?>
        <?php endif; ?>
        
		    <h6><?php _e( 'Course Reader', 'responsive' ); ?></h6>
            <div id="content">  
            <?php if ( !is_user_logged_in() ) { ?>
            <p><strong>Note:</strong> You're not logged in so your favourites and read items will not be saved. <a href="/login/">Login</a> or <a href="/login/?action=register">Register</a></p>
            <?php } ?>
            <p><small>Below is content posted outside the course on participants own blogs or to other social networks. Click on titles to load the content. If you would like your content added/removed from this please contact us.</small></p> 
               <div id="accordionLoader" class="inifiniteLoader">Loading... </div>
              <div id="accordion">        
        		<?php  get_template_part( 'content', get_post_format() ); ?> 
               </div>
            </div>
    
        </div><!-- end of #content-archive -->
<?php endif; ?> 
<?php get_sidebar('reader'); ?>
<?php get_footer(); ?>
