<article id="post-<?php the_ID(); ?>" <?php $this->classes('article'); ?>>
	<header <?php $this->classes('entry-header'); ?>>
		<h1 <?php $this->classes('* entry-title'); ?>>
			<span class='entry_title_a' style='color:#222222;text-decoration:none;'>
<?php if (isset($this->build->_the_title)) echo $this->build->_the_title; else $this->the_title(); ?>
			</span>
		</h1>
		<div <?php $this->classes('* entry-meta'); ?>>
			<span <?php $this->classes('*'); ?>>Generated on</span>
			<span <?php $this->classes('* a entry-meta_a'); ?> href="#" title="<?php echo esc_attr( mysql2date(get_option( 'time_format' ), current_time('mysql')) ); ?>" rel="bookmark">
				<time datetime="<?php echo esc_attr( mysql2date('c', current_time('mysql'))); ?>" pubdate>
					<?php echo mysql2date(get_option( 'date_format' ), current_time('mysql')); ?>
				</time>
			</span>
		</div>
	</header><!-- .entry-header -->

	<div <?php $this->classes('* entry-summary'); ?>>
<?php if (isset($this->build->_the_content)) echo $this->build->_the_content; else $this->the_content(); ?>
	</div><!-- .entry-summary -->

	<footer <?php $this->classes('entry-header entry-meta'); ?>>
<?php if (isset($this->build->_the_actions)) : ?>
	<div <?php $this->classes('nopmb entry-utility'); ?>>
		<span <?php $this->classes('*'); ?>>
			<span <?php $this->classes('*'); ?>>
<?php echo $this->build->_the_actions; ?>
			</span>
		</span>
	</div>
<?php endif; ?>
	</footer><!-- #entry-utility -->	
</article>