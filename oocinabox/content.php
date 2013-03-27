<?php while (have_posts()) : the_post(); ?>
<?php $source = html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8'); ?>
	<h3 class="post-title <?php if(function_exists('readerlite_get_if_read_post')){ readerlite_get_if_read_post(get_the_ID()); }?>"><?php wpfp_link() ?>
      <div class="ajaxed" id="<?php the_ID();?>" url="<?php the_permalink();?>">
      	<div><a class="jump_to_url disable_accordion" href="<?php the_permalink(); ?>" title="Open in new window: <?php the_title(); ?>" target="_blank"></a></div>
        <div id="post_title_block">
        	<span class="collapse_title"><?php echo html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8'); ?></span>
            <span class="collapse_source"> - <?php print $source; ?></span>
        </div>
      </div></h3>
	<div id="post-<?php the_ID();?>">
    	<div class="loaded-post"><div class="inifiniteLoader">Loading... </div></div>
    </div>
<?php endwhile; ?> 


			
          