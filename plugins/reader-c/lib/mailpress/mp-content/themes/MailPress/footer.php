<!-- start footer -->
				</div>
<?php //$this->get_sidebar(); ?>
			</div>
			<div style='clear:both;'></div>
			<table <?php $this->classes('nopmb ftable'); ?>>
				<tr>	
					<td <?php $this->classes('fltd'); ?>>
						<b>
							This mail is brought to you by MailPress.
						</b>
					</td>
					<td <?php $this->classes('frtd'); ?>>
						<b>
							MAIL IS SHARING POETRY
						</b>
					</td>	
				</tr>
			</table>
		</div>
<?php if (isset($this->args->unsubscribe)) { ?>
			<div <?php $this->classes('mail_link'); ?>>
				<a href='{{unsubscribe}}'  <?php $this->classes('mail_link_a a'); ?>>Manage your subscriptions</a>
			</div>
<?php } ?>
		</div>
	</body>
</html>