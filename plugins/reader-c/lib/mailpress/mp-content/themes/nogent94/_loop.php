<div>
	<table <?php $this->classes('nopmb w100'); ?>>
<?php while (have_posts()) : the_post(); ?>
		<tr>
			<td <?php $this->classes('nopmb w100 333'); ?>>
				<div style='margin:0pt 0pt 10px;text-align:justify;'>
					<h2 style='margin:30px 0pt 0pt;text-decoration:none;color:#333;font-size:1.5em;font-weight:bold;'>
						<a style='color:#333;' href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
<?php the_title(); ?>
						</a>
					</h2>
					<small style='color:#777;font-family:Arial,Sans-Serif;font-size:0.8em;line-height:1.5em;'>
<?php the_time('F j, Y') ?>
					</small>
					<div style='font-size:1em;'>
<?php $this->the_content( ' (suite...)' ); ?>
					</div>
				</div>
			</td>
		</tr>
<?php endwhile; ?>
	</table>
</div>