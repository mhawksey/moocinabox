<?php while (have_posts()) : the_post(); ?>
<?php $source = html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8'); ?>
	<h3 class="post-title <?php if(function_exists('readerlite_get_if_read_post')){ readerlite_get_if_read_post(get_the_ID()); }?>"><?php wpfp_link() ?>
      <div class="ajaxed" id="<?php the_ID();?>" url="<?php the_permalink();?>">
      	<div><a class="jump_to_url disable_accordion" href="<?php the_permalink(); ?>" title="Open in new window: <?php the_title(); ?>" target="_blank"></a></div>
        <div id="post_title_block">
        	<span class="collapse_title"><?php echo html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8'); ?></span>
            <span class="collapse_source"> - <?php print $source; ?></span>
            <div class="snippet"><?php the_excerpt(); ?></div>
        </div>
      </div></h3>
	<div id="post-<?php the_ID();?>">
       <div class="loaded-post">
			<div class="post-meta">
			<?php responsive_post_meta_data(); ?> from <a href="<?php the_syndication_source_link(); ?>" target="_blank"><?php print $source; ?></a><br/>
			<?php the_tags(__('Tagged with:', 'responsive') . ' ', ', ', '<br />'); ?> 
			<?php printf(__('Posted in %s', 'responsive'), get_the_category_list(', ')); ?>
			<!-- end of .post-data -->  
		   </div>
		   <div class="post-entry">
			 <?php the_content(__('Read more &#8250;', 'responsive')); ?>
		   </div>
		  </div>
		  <div sytle="clear:both"></div>
          <?php 
		    $posturlen = urlencode(get_permalink());
			$title = html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8');
			$titleen = rawurlencode($title);
		    $buttons = '<div class="share_widget post-'.$post_id.'">Share: '  
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://plus.google.com/share?url='.$posturlen.'\')" ><img src="https://www.gstatic.com/images/icons/gplus-16.png" alt="Share on Google+"/></a><span class="share-count"><i></i><u></u><span id="gp-count">--</span></span>'
            .' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.facebook.com/sharer.php?u='.$posturlen.'\')" >Facebook</a><span class="share-count"><i></i><u></u><span id="fb-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://twitter.com/intent/tweet?text='.$titleen.'%20'.$posturlen.'\')">Twitter</a><span class="share-count"><i></i><u></u><span id="tw-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.linkedin.com/shareArticle?mini=true&url='.$posturlen.'&source=MASHe&summary='.$titleen.'\')" >LinkedIn</a><span class="share-count"><i></i><u></u><span id="li-count">--</span></span>'
			.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://delicious.com/post?v=4&url='.$posturlen.'\')" >Delicious</a><span class="share-count"><i></i><u></u><span id="del-count">--</span></span>';
			echo $buttons;
		 ?>
    </div></div>
<?php endwhile; ?> 


			
          