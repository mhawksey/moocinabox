<?php while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php $this->classes('article'); ?>>
	<header <?php $this->classes('* entry-header'); ?>>
		<h1 <?php $this->classes('* entry-title'); ?>><a <?php $this->classes('* entry-title_a'); ?> href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
		<div <?php $this->classes('* entry-meta'); ?>>
			<span style='line-height:24px;'>Posted on</span>
			<a <?php $this->classes('entry-meta_a'); ?> href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_time() ); ?>" rel="bookmark"><time class='hover_underline'  datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" pubdate><?php echo esc_html( get_the_date() ); ?></time></a>
			<span style='line-height:24px;'>
				<span <?php $this->classes('*'); ?>> by </span>
				<span <?php $this->classes('*'); ?>>
					<a <?php $this->classes('* entry-meta_a'); ?> href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" title="<?php printf( __( 'View all posts by %s', 'twentyten' ), get_the_author() ); ?>"><?php the_author(); ?></a>
				</span>
			</span>
		</div>
	</header><!-- .entry-header -->

	<div <?php $this->classes('* entry-content'); ?>>
		<?php the_excerpt(); ?>
	</div><!-- .entry-content -->

	<footer <?php $this->classes('entry-footer'); ?>>
<?php
$show_sep = false;

// Categories

if ( $categories_list = get_the_category_list( __( ', ', 'twentytwelve' ) ) )
{
	if ( $show_sep ) echo ' ';
	printf( '<span><span style="font-size:12px;color:#777;font-style:italic;">Posted in</span> %1$s</span>', $categories_list );
	$show_sep = true;
}
// End categories

// Tags
if ( $tags_list = get_the_tag_list( '', ', ' ) )
{
	if ( $show_sep ) echo ' '; 
	printf( '<span><span style="font-size:12px;color:#777;font-style:italic;">Tagged</span> %1$s</span>', $tags_list );
	$show_sep = true;
}
// End tags

// Comments
if ( comments_open() )
{	if ( $show_sep ) echo ' '; 
?>
		<span><?php comments_popup_link( '<span style="font-size:12px;color:#777;font-style:italic;">' . __( 'Leave a reply', 'twentyeleven' ) . '</span>', __( '<b>1</b> Reply', 'twentyeleven' ), __( '<b>%</b> Replies', 'twentyeleven' ), 'hover_underline' ); ?></span>
<?php 
}
// End comments 
?>
	</footer>
</article><!-- #post-<?php the_ID(); ?> -->
<?php 
endwhile;