<?php
/*
Template Name: MailPress Archives
*/
?>
<?php get_header(); ?>
	<div id='content'>
<?php
if (class_exists('MailPress'))
{
	$m = new MP_Query();
	$m->query();
	while ($m->have_mails()) : $m->the_mail(); 
?>
		<div id="mail-<?php $m->the_ID(); ?>">
			<h2 class="entry-title"><a href="<?php $m->the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), $m->get_the_subject() ); ?>" rel="bookmark"><?php $m->the_subject(); ?></a></h2>

			<div class="entry-meta">
				Mailed on <?php $m->the_date(); ?>
			</div><!-- .entry-meta -->
			<div class="entry-content">
				<?php //$m->the_content(); ?>
			</div><!-- .entry-content -->
			<div class="entry-utility">
			</div><!-- .entry-utility -->
		</div><!-- #mail-## -->
<?php 
	endwhile;
}
else
{
	echo "<div>Sorry, MailPress is not available !</div>";
}
?>
	</div>
<?php get_footer(); ?>

