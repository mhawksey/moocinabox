  <?php if ( bp_has_groups( 'type=' . $type . '&max=' . $max ) ) : ?>
			<ul id="groups-list" <?php $this->classes('nopmb item-list'); ?>>
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li style="padding: 5px 0;">
                    	<div class="item-avatar" <?php $this->classes('nopmb item-avatar'); ?>>
							<a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_avatar_mini() ?></a>
						</div>
						<div style="margin-left: 40px;">
							<div class="item-title" style="margin-bottom:5px;"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta">
								<span>
								<?php
									if ( 'newest' == $type )
										printf( __( 'created %s', 'buddypress' ), bp_get_group_date_created() );
									if ( 'active' == $type )
										printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() );
									else if ( 'popular' == $type )
										bp_group_member_count();
								?>
								</span>
							</div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
	<?php endif; ?>
            