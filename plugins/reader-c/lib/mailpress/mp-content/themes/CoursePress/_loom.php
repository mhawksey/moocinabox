<table <?php $this->classes('nopmb ctable'); ?>>

	<tr>

		<td <?php $this->classes('nopmb ctd'); ?>>

			<div <?php $this->classes('cdiv'); ?>>

				<h2 <?php $this->classes('ch2'); ?>>

<?php if (isset($this->build->_the_title)) echo $this->build->_the_title; else $this->the_title(); ?>

				</h2>

				<small <?php $this->classes('nopmb cdate'); ?>>

<?php echo mysql2date('F j, Y', current_time('mysql')); ?>

				</small>

				<div <?php $this->classes('nopmb'); ?>>

<?php if (isset($this->build->_the_content)) echo $this->build->_the_content; else $this->the_content(); ?>

				</div>

				<div <?php $this->classes('nopmb'); ?>>

<?php echo (isset($this->build->_the_actions)) ? $this->build->_the_actions : '&#160;'; ?>

				</div>

			</div>

		</td>

	</tr>

</table>