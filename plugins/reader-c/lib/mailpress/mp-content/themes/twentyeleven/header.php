<?php $post_id = (isset($this->args->newsletter['params']['post_id'])) ? $this->args->newsletter['params']['post_id'] : false; ?>
<?php $this->get_template_part('head'); ?>
	<body>
		<div  style='padding:0;margin:0;background: none repeat scroll 0 0 #E2E2E2;'>
			<div <?php $this->classes('* body'); ?>>
<?php if (isset($this->args->viewhtml)) { ?>
				<div <?php $this->classes('mail_link'); ?>>
					Email not displaying correctly ? <a href='{{viewhtml}}' <?php $this->classes('mail_link_a a'); ?>>View it in your browser</a>
				</div>
<?php } ?>
				<div <?php $this->classes('* page'); ?>>
					<header <?php $this->classes('branding'); ?> role="banner">
						<div <?php $this->classes('branding_hgroup'); ?>>
							<h1 <?php $this->classes('* h1'); ?>><span <?php $this->classes('*'); ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"  <?php $this->classes('h1_a'); ?>><?php bloginfo( 'name' ); ?></a></span></h1>
							<h2 <?php $this->classes('* h2'); ?>><?php bloginfo( 'description' ); ?></h2>
						</div>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php $this->classes('* h_img_a'); ?>>
							<img style='float:left;width:760px;' src="<?php do_action('MailPress_theme_html_header_image', 'images/header-1.jpg', $post_id); ?>" style="width:760px;" alt="img" />
						</a>
						<nav <?php $this->classes('nav'); ?> role="navigation">
							<ul style="font-size:13px;list-style:none outside none;margin:0 7.6%;padding-left:0;"><li><a style="color:#EEE;display:block;line-height:3.333em;padding:0 1.2125em;text-decoration:none;">&#160;</a></li></ul>
						</nav><!-- #access -->
					</header><!-- #branding -->
					<div <?php $this->classes('main'); ?>>
						<div <?php $this->classes('* primary'); ?>>
							<div <?php $this->classes('* content'); ?>>
<!-- end header -->