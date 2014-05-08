<!-- start sidebar -->

<div <?php $this->classes('sidediv'); ?>>

	  <table <?php $this->classes('sidetable'); ?>  cellspacing='0' cellpadding='0'>

		<tr>

			<td  style="color:#333333;font-family:Verdana,Sans-Serif;font-size:1.4em;font-weight:bold;">

Last Posts

			</td>

		</tr>

		<tr>

			<td>

				<?php query_posts('showposts=10'); ?>

				<ul <?php $this->classes('sideul'); ?>>

					<?php while (have_posts()) : the_post(); ?>

						<li style="list-style-type:disc;"><a <?php $this->classes('sidelink'); ?> href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></li>

					<?php endwhile; ?>

				</ul>

			</td>

		</tr>

	</table>

</div>

<!-- end sidebar -->