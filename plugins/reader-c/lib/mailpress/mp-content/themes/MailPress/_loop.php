<table <?php $this->classes('nopmb ctable'); ?>>
<?php while (have_posts()) : the_post(); ?>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?>>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
					<a <?php $this->classes('clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
<?php the_title(); ?>
					</a>
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php the_time('F j, Y') ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
<?php $this->the_content( __( '(more...)' ) ); ?>
				</div>
			</div>
		</td>
	</tr>
<?php endwhile; ?>
</table>