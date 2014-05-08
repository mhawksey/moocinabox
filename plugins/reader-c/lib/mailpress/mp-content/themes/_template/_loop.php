<?php
while (have_posts()) : the_post(); 
?>

<div id="post-<?php the_ID(); ?>">
	<div>
		<h2>
			<a href="<?php the_permalink(); ?>" title="<?php printf( 'Permalink to %s', the_title_attribute('echo=0') ); ?>" rel="bookmark">
<?php $this->the_title(); ?>
			</a>
		</h2>
	</div>
			
	<div>
		<span>Posted on </span>
		<a href="<?php the_permalink(); ?>" title="<?php the_time('Y-m-d\TH:i:sO') ?>" rel="bookmark">
			<span>
<?php the_time( get_option( 'date_format' ) ); ?>
			</span>
		</a>
		<span> by
		</span>
		<span>
			<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( 'View all posts by %s', get_the_author() ); ?>">
<?php the_author(); ?>
			</a>
		</span>					
	</div><!-- .entry-meta -->
								
	<div>	
<?php $this->the_content(  sprintf( 'Continue reading %s', '<span class="meta-nav">&rarr;</span>') ); ?>
<?php wp_link_pages('before=<div>Pages:&after=</div>') ?>
	</div><!-- .entry-content -->

	<div>
		<span>
			<span> Posted in 
			</span>
			<?php echo get_the_category_list(', '); ?>
		</span>
		<span> | </span>
		<?php the_tags( '<span><span>Tagged </span>', ", ", '</span><span>|</span>' ) ?>
		<span><?php comments_popup_link( 'Leave a comment', '1 Comment', '% Comments' ); ?></span>
	</div><!-- #entry-utility -->	
</div><!-- #post-<?php the_ID(); ?> -->

<?php 
endwhile;