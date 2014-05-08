<?php while (have_posts()) : the_post(); ?>
      <?php do_action( 'bp_before_blog_post' ); ?>
      <?php 
	  $source = html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8'); ?>
      <h3 class="post-title <?php Reader_C::readerlite_get_if_read_post(get_the_ID()); ?>">
      <?php if(is_user_logged_in()):?><div id="reaction"><?php my_bp_fav_button(get_the_ID());?></div><?php endif; ?>
        <div class="ajaxed" id="<?php the_ID();?>" url="<?php the_permalink();?>">
          <div><a class="jump_to_url disable_accordion" href="<?php the_permalink(); ?>" title="Open in new window: <?php the_title(); ?>" target="_blank"></a></div>
          <div id="post_title_block"> <span class="collapse_title"><?php echo html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8'); ?></span> <span class="collapse_source"> - <?php print $source; ?></span>
            <div class="snippet"><?php echo Reader_C::reader_excerpt(get_the_ID()); ?></div>
          </div>
        </div>
      </h3>
      <div id="post-<?php the_ID();?>">
        <div class="loaded-post">
          <h2 class="posttitle"><a href="<?php the_permalink(); ?>" target="_blank">
            <?php the_title(); ?>
            </a></h2>
          <p class="postmetadata"> <?php printf( __( 'Posted on %1$s' , 'buddypress' ), get_the_date() ); ?>
            <?php if (Reader_C::get_user_role( $post->post_author )!="contributor"){ printf( _x( 'by %s', 'Post written by...', 'buddypress' ), bp_core_get_userlink( $post->post_author ) );} ?>
            from <a href="<?php the_syndication_source_link(); ?>" target="_blank"><?php print $source; ?></a><br/>
            <?php the_tags( '<span class="tags">' . __( 'Tags: ', 'buddypress' ), ', ', '</span>' ); ?>
            <?php //printf(__('Posted in %s', 'responsive'), get_the_category_list(', ')); ?>
            <!-- end of .post-data --> 
          </p>
          <div class="entry">
            <?php the_content( __( 'Read the rest of this entry &rarr;', 'buddypress' ) ); ?>
          </div>
        </div>
        <div sytle="clear:both"></div>
        <?php 
		    $posturlen = urlencode(get_permalink());
			$title = html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8');
			$titleen = rawurlencode($title);
		    $buttons = '<div class="share_widget post-'.get_the_ID().'">Share: '  
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://plus.google.com/share?url='.$posturlen.'\')" title="Click to share on Google+" ><i class="fa fa-google-plus-square" style="color:#d34836;"></i></a><span class="share-count"><i></i><u></u><span id="gp-count">--</span></span>'
            .' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.facebook.com/sharer.php?u='.$posturlen.'\')" title="Click to share on Facebook"><i class="fa fa-facebook-square" style="color:#4C66A4;"></i></a><span class="share-count"><i></i><u></u><span id="fb-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://twitter.com/intent/tweet?text='.$titleen.'%20'.$posturlen.'\')" title="Click to share on Twitter"><i class="fa fa-twitter-square" style="color:#4099FF;"></i></a><span class="share-count"><i></i><u></u><span id="tw-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.linkedin.com/shareArticle?mini=true&url='.$posturlen.'&source=ALT&summary='.$titleen.'\')"  title="Click to share on LinkedIn"><i class="fa fa-linkedin-square" style="color:#007bb6;"></i></a><span class="share-count"><i></i><u></u><span id="li-count">--</span></span>';
			echo $buttons;
		 ?>
      </div>
    </div>
    <?php do_action( 'bp_after_blog_post' ); ?>
    <?php endwhile; ?>