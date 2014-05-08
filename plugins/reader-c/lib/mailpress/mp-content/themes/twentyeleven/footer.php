<!-- start footer -->
							</div>
						</div>
					</div><!-- #main -->

					<footer <?php $this->classes('footer'); ?> role="contentinfo">
<?php
	/* A sidebar in the footer? Yep. You can can customize
	 * your footer with three columns of widgets.
	 * file should be named sidebar-footer.php
	 */
	//$this->get_sidebar( 'footer' );
?>
						<div  <?php $this->classes('* site-generator'); ?>>
							<a style='color: #555;font-weight: bold;' href="<?php echo esc_url( 'http://blog.mailpress.org/' ); ?>" title="<?php esc_attr_e( 'The WordPress Mailing Plugin' ); ?>"><?php printf( 'Proudly mailed by %s', 'MailPress' ); ?></a>
						</div>
					</footer><!-- #colophon -->
				</div><!-- #page -->
<?php if (isset($this->args->unsubscribe)) { ?>
				<div <?php $this->classes('mail_link'); ?>>
					<a href='{{unsubscribe}}'  <?php $this->classes('mail_link_a a'); ?>>Manage your subscriptions</a>
				</div>
<?php } ?>
			</div><!-- #body -->
		</div>
	</body>
</html>
