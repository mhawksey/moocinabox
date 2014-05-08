<!-- start footer -->
						</div>
					</div>
				</div>
				<div <?php $this->classes('nopmb w100'); ?>>
					<div <?php $this->classes('colophon'); ?>>
						<table <?php $this->classes('nopmb w100'); ?>>
							<tr>
								<td <?php $this->classes('nopmb'); ?>>
									<div <?php $this->classes('nopmb site-info'); ?>>
<a <?php $this->classes('nopmb site-info_a'); ?> href="<?php bloginfo( 'url' ) ?>/" title="<?php bloginfo( 'name' ) ?>" rel="home"><?php bloginfo( 'name' ) ?></a>
									</div>
								</td>
								<td <?php $this->classes('nopmb'); ?>>
									<table style='width:auto;float:right'>
										<tr>
											<td <?php $this->classes('nopmb wauto'); ?>>
												<span <?php $this->classes('nopmb site-generator'); ?>>
Proudly mailed by 
												</span>
											</td>
											<td <?php $this->classes('nopmb wauto'); ?>>
												<img src='images/mailpress.png' <?php $this->classes('powered_img'); ?> alt='img' />
											</td>
											<td <?php $this->classes('nopmb wauto'); ?>>
												<a <?php $this->classes('powered_a'); ?> href="http://mailpress.org/" title="The WordPress Mailing plugin">
MailPress
												</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>
<?php if (isset($this->args->unsubscribe)) { ?>
			<div <?php $this->classes('mail_link'); ?>>
				<a href='{{unsubscribe}}'  <?php $this->classes('mail_link_a a'); ?>>Manage your subscriptions</a>
			</div>
<?php } ?>
			</div>
		</div>
	</body>
</html>