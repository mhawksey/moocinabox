<?php
/*
Category-xx
*/
?>
<?php if (class_exists('MailPress')) $results = MP_Mail_links::process(); ?>
<?php get_header(); ?>

	<div id='content' class='narrowcolumn'>
		<div class='post' id='post-MailPress'>
		<h2><?php echo $results ['title']; ?></h2>
			<div class='entry'>
				<?php echo $results ['content']; ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>