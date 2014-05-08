<!-- start footer -->
						</div>
						<div style='clear:both;'></div>
					</div>
				</div>

				<div style='text-align:center;color:#D07B40;background-color:#e9e9e9;margin:20px;padding:5px;'>
					<small>
						Vous recevez cet e-mail car vous avez donn&eacute; votre accord pour recevoir des informations sur support &eacute;lectronique de la part de <?php bloginfo( 'name' ); ?>.
						<br />
						Conform&eacute;ment aux dispositions de la loi 'informatique et libert&eacute;s' du 6 janvier 1978, vous disposez d'un droit d'acc&egrave;s et de rectification aux donn&eacute;es personnelles vous concernant que vous pouvez exercer en &eacute;crivant &agrave; <a style='color:#999;' href='mailto:<?php  bloginfo( 'admin_email' ); ?>'><?php  bloginfo( 'admin_email' ); ?></a>
						<br />
						Pour en savoir plus : <a style='color:#999;' href='www.cnil.fr'>www.cnil.fr</a>
					</small>
				</div>
<?php if (isset($this->args->unsubscribe)) { ?>
				<div style='text-align:center;padding-bottom:10px;'>
					<small>
						Pour se d&eacute;sinscrire :
						<br />
						Il vous suffit d'activer 
						<a href='{{unsubscribe}}' <?php $this->classes('mail_link_a a'); ?>>
							ce lien
						</a>.
					</small>
				</div>
<?php } else { ?>
				<br />
<?php } ?>
			</div>
		</div>
	</body>
</html>

