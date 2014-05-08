<div>
	<div>
		<h2>
<?php if (isset($this->build->_the_title)) echo $this->build->_the_title; else $this->the_title(); ?>
		</h2>
	</div>
			
	<div>
		<span><?php printf('Generated on %s', mysql2date(get_option( 'date_format' ), current_time('mysql'))); ?></span>
	</div>
			
	<div>	
		<br />
<?php if (isset($this->build->_the_content)) echo $this->build->_the_content; else $this->the_content(); ?>
		<br />
	</div>

<?php if (isset($this->build->_the_actions)) : ?>
	<div>
		<span>
			<span>
<?php echo $this->build->_the_actions; ?>
			</span>
		</span>
	</div>
<?php endif; ?>
</div>