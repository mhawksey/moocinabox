<article id="post-<?php the_ID(); ?>" <?php $this->classes('article'); ?>>
	<header <?php $this->classes('* entry-header'); ?>>
		<h1 <?php $this->classes('* entry-title'); ?>><span <?php $this->classes('* entry-title_a'); ?>><?php if (isset($this->build->_the_title)) echo $this->build->_the_title; else $this->the_title(); ?></span></h1>
		<div <?php $this->classes('* entry-meta'); ?>>
			<span style='line-height:24px;'>Generated on</span>
			<span <?php $this->classes('entry-meta_a'); ?>><time class='hover_underline'  datetime="<?php echo esc_attr( mysql2date('c', current_time('mysql'))); ?>" pubdate><?php echo mysql2date(get_option( 'date_format' ), current_time('mysql')); ?></time></span>
		</div>
	</header><!-- .entry-header -->

	<div <?php $this->classes('* entry-content'); ?>>
<?php if (isset($this->build->_the_content)) echo $this->build->_the_content; else $this->the_content(); ?>
	</div><!-- .entry-content -->

	<footer <?php $this->classes('entry-footer'); ?>>
<?php if (isset($this->build->_the_actions)) : ?>
		<span>
			<span>
<?php echo $this->build->_the_actions; ?>
			</span>
		</span>
	</div>
<?php endif; ?>
	</footer>	
</article>