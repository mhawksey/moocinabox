<?php $post_id = (isset($this->args->newsletter['params']['post_id'])) ? $this->args->newsletter['params']['post_id'] : false; ?>
<?php $this->get_template_part('head'); ?>
	<body style='padding:0;margin:0;'>
		<div <?php $this->classes('* body'); ?>>
<?php if (isset($this->args->viewhtml)) { ?>
			<div <?php $this->classes('mail_link'); ?>>
				Email not displaying correctly ? <a href='{{viewhtml}}' <?php $this->classes('mail_link_a a'); ?>>View it in your browser</a>
			</div>
<?php } ?>
			<div <?php $this->classes('* page'); ?>>
				<header <?php $this->classes('* site-header'); ?> role="banner">
					<hgroup <?php $this->classes('* hgroup'); ?>>
						<h1 <?php $this->classes('* site-title'); ?>>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"  <?php $this->classes('site-title_a'); ?>>
								<?php bloginfo( 'name' ); ?>
							</a>
						</h1>
						<h2 <?php $this->classes('* site-description'); ?>><?php bloginfo( 'description' ); ?></h2>
					</hgroup>
					<nav <?php $this->classes('* cb nav'); ?> role="navigation">
						<div <?php $this->classes('*'); ?>>
							<ul <?php $this->classes('* nav-ul'); ?>>
								<li <?php $this->classes('* nav-li'); ?>><span style='line-height:48px;'>&#160;</span></li>
							</ul>
						</div>
					</nav><!-- #access -->
					<img src="<?php do_action('MailPress_theme_html_header_image', 'images/header-1.jpg', $post_id); ?>" style="width:760px;" alt="img" />
				</header><!-- #branding -->
				<div <?php $this->classes('* cb'); ?>>
					<div <?php $this->classes('* primary'); ?>>
						<div <?php $this->classes('*'); ?>>
<!-- end header -->