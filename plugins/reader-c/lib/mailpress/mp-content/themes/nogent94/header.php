<?php $this->get_template_part('head'); ?>
	<body>
		<div <?php $this->classes('body'); ?>>
			<div <?php $this->classes('wrapper'); ?>>
				<div>
<?php if (isset($this->args->viewhtml)) { ?>
			<div <?php $this->classes('mail_link'); ?>>
				Si ce mail ne s'affiche pas correctement <a href='{{viewhtml}}' <?php $this->classes('mail_link_a a'); ?>>ouvrir ce lien</a>
			</div>
<?php } ?>
					<div>
						<img src='Nogent94.gif' style='border:none;margin:20px 0;padding:0' alt='img' />
						<img src='degrade.jpg' style='width:100%;max-height:25px;border:none;padding:5px 0;' alt='img' />
						<span style='float:left;padding:0;margin:0;'><small><b><a href='<?php echo site_url(); ?>' style='color:#D76716;text-align:left;text-decoration:none;outline-style:none;'><?php echo site_url(); ?></a></b></small></span>
						<span style='float:right;color:#590000'><small><b><?php echo mysql2date('l j F Y', current_time('mysql')); ?></b></small></span>
					</div>
					<div style='clear:both;'></div>
				</div>
				<div <?php $this->classes('main'); ?>>
					<div <?php $this->classes('nopmb w100'); ?>>
						<div <?php $this->classes('content'); ?>>
<!-- end header -->