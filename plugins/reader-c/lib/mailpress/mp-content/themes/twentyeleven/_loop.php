<?php while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php $this->classes('article'); ?>>
	<header <?php $this->classes('entry-header'); ?>>
		<h1 <?php $this->classes('* entry-title'); ?>><a class='entry_title_a' style='color:#222222;text-decoration:none;' href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyeleven' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
		<div <?php $this->classes('* entry-meta'); ?>>
			<span <?php $this->classes('*'); ?>>Posted on</span>
			<a <?php $this->classes('* a entry-meta_a'); ?> href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_time() ); ?>" rel="bookmark">
				<time class='hover_underline'  datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" pubdate>
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</a>
		</div>

		<?php if ( comments_open() && ! post_password_required() ) : ?>
		<div <?php $this->classes('*'); ?> class='comments-link-bubble'>
			<?php comments_popup_link( '<span class="leave-reply">' . __( 'Reply', 'twentyeleven' ) . '</span>', _x( '1', 'comments number', 'twentyeleven' ), _x( '%', 'comments number', 'twentyeleven' ) ); ?>
		</div>
		<?php endif; ?>
	</header><!-- .entry-header -->

	<div <?php $this->classes('* entry-summary'); ?>>
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->

	<footer <?php $this->classes('entry-header entry-meta'); ?>>
<?php 
$show_sep = false;
$categories_list = get_the_category_list( __( ', ', 'twentyeleven' ) );
if ( $categories_list ):
	$show_sep = true;
?>
		<span <?php $this->classes('*'); ?>>
			<span <?php $this->classes('*'); ?>>
				Posted in
			</span>
			<?php echo $categories_list; ?>
		</span>
<?php 
endif; // End if categories

$tags_list = get_the_tag_list( '', ', ' );
if ( $tags_list ):
	if ( $show_sep ) : 
?>
		<span <?php $this->classes('*'); ?>> | </span>
<?php 
	endif; // End if $show_sep
	$show_sep = true;
?>
		<span <?php $this->classes('*'); ?>>
			<span <?php $this->classes('*'); ?>>
				Tagged
			</span>
			<?php echo $tags_list; ?>
		</span>
<?php 
endif; // End if $tags_list 

if ( comments_open() ) :
	if ( $show_sep ) :
 ?>
		<span <?php $this->classes('*'); ?>> | </span>
<?php 
	endif; // End if $show_sep
?>
		<span <?php $this->classes('*'); ?>><?php comments_popup_link( '<span ' . $this->classes('*', 0) . '>' . __( 'Leave a reply', 'twentyeleven' ) . '</span>', __( '<b>1</b> Reply', 'twentyeleven' ), __( '<b>%</b> Replies', 'twentyeleven' ), 'hover_underline' ); ?></span>
<?php 
endif; // End if comments_open() 
?>
	</footer><!-- #entry-utility -->	
</article><!-- #post-<?php the_ID(); ?> -->

<?php 
endwhile;