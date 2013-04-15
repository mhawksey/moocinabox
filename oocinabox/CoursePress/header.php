<?php $this->get_template_part('head'); ?>

	<body>

		<div <?php $this->classes('body'); ?>>

			<div <?php $this->classes('wrapper'); ?>>

<?php if (isset($this->args->viewhtml)) { ?>

			<div <?php $this->classes('mail_link'); ?>>

				Email not displaying correctly ? <a href='{{viewhtml}}' <?php $this->classes('mail_link_a a'); ?>>View it in your browser</a>

			</div>

<?php } ?>

				<table <?php $this->classes('nopmb htable htr'); ?>>	

					<tr>

						<td <?php $this->classes('nopmb txtleft'); ?>>

							<img src='http://octel.alt.ac.uk/wp-content/uploads/2012/11/cropped-cropped-OCTEL-logo-ALT-green-1.png' <?php $this->classes('logo'); ?> alt='img' />

						</td>

					</tr>

				</table>

				<table <?php $this->classes('nopmb htdate'); ?>>

					<tr>

						<td <?php $this->classes('hdate'); ?>>

							<?php echo mysql2date('F j, Y', current_time('mysql')); ?>

						</td>

					</tr>

				</table>

				<div  <?php $this->classes('main'); ?>>

					<div  <?php $this->classes('content'); ?>>

<!-- end header -->