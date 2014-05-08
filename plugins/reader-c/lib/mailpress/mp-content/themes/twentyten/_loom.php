<div <?php $this->classes('entry'); ?>>
	<div>
		<h2 <?php $this->classes('entry-title'); ?>>
<?php if (isset($this->build->_the_title)) echo $this->build->_the_title; else $this->the_title(); ?>
		</h2>
	</div>
			
	<div <?php $this->classes('nopmb entry-meta'); ?>>
		<span <?php $this->classes('nopmb'); ?>>
<?php _e('Generated on ', 'twentyten'); ?>
<?php echo mysql2date(get_option( 'date_format' ), current_time('mysql')); ?>
		</span>
	</div>
			
	<div <?php $this->classes('nopmb'); ?>>	
<?php if (isset($this->build->_the_content)) echo $this->build->_the_content; else $this->the_content(); ?>
	</div>

<?php if (isset($this->build->_the_actions)) : ?>
	<div <?php $this->classes('nopmb entry-utility'); ?>>
		<span <?php $this->classes('nopmb entry-sep'); ?>>
			<span  <?php $this->classes('nopmb entry-sep'); ?>>
<?php echo $this->build->_the_actions; ?>
			</span>
		</span>
	</div>
<?php endif; ?>
</div>