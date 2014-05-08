<table <?php $this->classes('nopmb cp_twotable'); ?>>
        <?php $info = false; ?>
        <?php 
        global $wp_query;
		$args = array_merge( $wp_query->query_vars, array( 'category_name' => 'blog-post' ) );
		$args['posts_per_page'] = -1;
	
	$my_query = new WP_Query($args);
	if ($my_query-> have_posts() ) :
		while ($my_query->have_posts()) : $my_query->the_post(); ?>
        <?php //while (have_posts()) : the_post(); ?>
        <?php //if (in_category($category_name)): ?>
        <?php $info = true; ?>
        <?php $col = ( 'left' != $col ) ? 'left' : 'right';
	  $content = get_the_excerpt();
	  $excerpt = explode(' ',$content); 
      if ($col == "left"){ ?>
        <tr>
          <?php } ?>
          <td <?php $this->classes('nopmb cp_onetd'); ?>><div <?php $this->classes('cp_onecell'); ?>>
              <h2 <?php $this->classes('cp_ch2'); ?>> <a <?php $this->classes('cp_clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
                <?php the_title(); ?>
                </a> </h2>
              <small <?php $this->classes('nopmb cp_cdate'); ?>>
              <?php the_time('F j, Y') ?>
              | <a href="<?php the_syndication_source_link(); ?>" target="_blank"><?php echo html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8') ?></a> </small>
              <div <?php $this->classes('nopmb cp_extext'); ?>>
                <?php //$this->the_content( __( '(more...)' ) ); ?>
                <?php $content = strip_tags(get_the_excerpt());
							  $content = str_replace(array("Read more &#8250;","[...]"),"",$content);
							  $words = array_slice(explode(' ', $content), 0, 50);
							  $new_ex = "";
							  foreach ($words as $word){
								if (strlen($word)<28){
									$new_ex .= $word .' ';
								} else {
									$new_ex .= substr($word,0,28) ."... ";
								}
							  }
							  
							  echo $new_ex; ?>
                <a href="<?php the_permalink() ?>">Read more &raquo;</a> </div>
            </div></td>
          <?php  if ($col != "left"){ ?>
        </tr>
        <?php } ?>
        <?php //endif; ?>
        <?php endwhile; ?>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
        <?php  if ($col == "left"){ ?>
        
          <td></td>
        </tr>
        <?php } ?>
        <?php if (!$info):?>
        <tr>
          <td><div <?php $this->classes('cp_cdiv'); ?>>
              <div <?php $this->classes('nopmb'); ?>>
                <p <?php $this->classes('nopmb noinfo'); ?>>No new participant posts in this newsletter</p>
              </div>
            </div></td>
        </tr>
        <?php endif; ?>
        <?php //rewind_posts(); ?>
      </table>