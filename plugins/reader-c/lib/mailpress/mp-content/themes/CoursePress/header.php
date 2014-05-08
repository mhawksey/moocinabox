<?php $this->get_template_part('head'); ?>

	<body>

		<div <?php $this->classes('body'); ?>>

			<div <?php $this->classes('wrapper'); ?>>

<?php if (isset($this->args->viewhtml)) { ?>

			<div <?php $this->classes('mail_link'); ?>>

				<p>Email not displaying correctly? <a href='{{viewhtml}}' <?php $this->classes('mail_link_a a'); ?>>View it in your browser</a></p>
                <p>Too many emails? <a href='{{unsubscribe}}'  <?php $this->classes('mail_link_a a'); ?>>manage your subscriptions</a></p>

			</div>

<?php } ?>

				<table <?php $this->classes('nopmb htable htr'); ?>>	

					<tr>

						<td <?php $this->classes('nopmb txtright'); ?>>

							<img src="<?php echo get_theme_mod( 'alt_logo' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" id="site-logo-image">

						</td>

					</tr>

				</table>

				<table <?php $this->classes('nopmb cp_htdate'); ?>>

					<tr>

						<td <?php $this->classes('cp_hdate'); ?>>

							<?php echo mysql2date('F j, Y', current_time('mysql')); ?>

						</td>

					</tr>

				</table>

				<div  <?php $this->classes('main'); ?>>

					<div  <?php $this->classes('content'); ?>>

<!-- end header -->