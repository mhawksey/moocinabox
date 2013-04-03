<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * Single Posts Template
 *
 *
 * @file           single.php
 * @package        Responsive 
 * @author         Emil Uzelac 
 * @copyright      2003 - 2013 ThemeID
 * @license        license.txt
 * @version        Release: 1.0
 * @filesource     wp-content/themes/responsive/single.php
 * @link           http://codex.wordpress.org/Theme_Development#Single_Post_.28single.php.29
 * @since          available since Release 1.0
 */
?>
<?php if (class_exists('MailPress')) $results = MP_Mail_links::process(); ?>
<?php get_header(); ?>

        <div id="content" class="grid col-620">
        	

		<div class='post' id='post-MailPress'>

		<h2><?php echo $results ['title']; ?></h2>

			<div class='entry'>

				<?php echo $results ['content']; ?>

			</div>

		</div>

	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>

<?php

/*

Template Name: MailPress

*/

?>
