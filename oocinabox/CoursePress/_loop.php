<?php 
global $wp_query;
print_r($wp_query->parse_query_vars());
$oldquery = $wp_query->query_vars; ?>
<?php $args = array_merge( $wp_query->query_vars, array( 'category_name' => 'reader' ) ); ?>
<?php $my_query = new WP_Query($args); ?>
<?php while ($my_query->have_posts()) : $my_query->the_post(); ?>
<?php
 $cats = get_the_category(); 
 foreach($cats as $c) {
	 //print_r($c);
	 if ($c->category_parent > 0) {
	 	$catcount[$c->cat_name] +=1;
		$catreplace[$c->cat_name.'</a>'] = $c->cat_name. '</a> (<strong>'. $catcount[$c->cat_name].'</strong>)';
	 }
 }
 ?>
<?php endwhile; ?>
 <?php
 $catlist = wp_list_categories('echo=0&show_count=0&title_li=&exclude=1');
 $catlist = strtr($catlist, $catreplace);
 $catlist = preg_replace('/<li[^>]*>/','<li style="margin:0">',$catlist);
 $catlist = preg_replace('/<ul[^>]*>/','<ul style="padding-left:20px;">',$catlist);
?> 


<table <?php $this->classes('nopmb ctable'); ?>>
<?php query_posts(array_merge( $oldquery, array( 'category_name' => 'course-information' ) )); ?>
	<tr>
		<td <?php $this->classes('nopmb csection'); ?> colspan="2">Course Information <small>(<a href="<?php echo site_url(); ?>/category/course-information">Visit on site</a>)</small></td>
    </tr>
<?php $info = false; ?>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?> colspan="2">
        	<div <?php $this->classes('csum'); ?>><strong>Recent Activity</strong> (numbers in brackets indicate new posts)<ul> <?php  echo $catlist; ?></ul>
            </div>
            
<?php while (have_posts()) : the_post(); ?>
<?php if (in_category('course-information')): ?>
<?php $info = true; ?>
			<div <?php $this->classes('cdiv'); ?>>
				<h2 <?php $this->classes('ch2'); ?>>
					<a <?php $this->classes('clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
<?php the_title(); ?>
					</a>
				</h2>
				<small <?php $this->classes('nopmb cdate'); ?>>
<?php the_time('F j, Y') ?>
				</small>
				<div <?php $this->classes('nopmb'); ?>>
<?php $this->the_content( __( '(more...)' ) ); ?>
				</div>
			</div>

<?php endif; ?>
<?php endwhile; ?>
<?php if (!$info):?>
			<div <?php $this->classes('cdiv'); ?>>
				<div <?php $this->classes('nopmb'); ?>>
					<p <?php $this->classes('nopmb noinfo'); ?>>No new course information in this newsletter</p>
				</div>
			</div>
<?php endif; ?>
		</td>
	</tr>
    
    <tr>
		<td <?php $this->classes('nopmb csection'); ?> colspan="2">Forum Activity <small>(<a href="<?php echo site_url(); ?>/forums">Visit on site</a>)</small></td>
    </tr>
    <tr>
       <td <?php $this->classes('nopmb ctd'); ?> width="50%">
        <div <?php $this->classes('cdiv'); ?>>
            <h2 <?php $this->classes('ch2'); ?>>Recent Topics (last 5)</h2>
            <? if(function_exists('get_bbp_recent_topics'))
					get_bbp_recent_topics();
			?>    
        </div>
	   </td>
       
       <td <?php $this->classes('nopmb ctd'); ?> width="50%">
        <div <?php $this->classes('cdiv'); ?>>
            <h2 <?php $this->classes('ch2'); ?>>Recent Replies (last 5)</h2>
            <? if(function_exists('get_bbp_recent_replies'))
					get_bbp_recent_replies();
			?>      
        </div>
	   </td>
    
    </tr>

<?php query_posts(array_merge( $oldquery, array( 'category_name' => 'blog-posts' ) )); ?>
	<tr>
		<td <?php $this->classes('nopmb csection'); ?> colspan="2">Participant Blog Posts  <small>(<a href="<?php echo site_url(); ?>/category/blog-posts">Visit on site</a>)</small></td>
    </tr>
<?php $info = false; ?>	
<?php while (have_posts()) : the_post(); ?>
<?php if (in_category('blog-posts')): ?>
<?php $info = true; ?>
<?php $col = ( 'left' != $col ) ? 'left' : 'right';
	  $content = get_the_excerpt();
	  $excerpt = explode(' ',$content); 
      if ($col == "left"){ ?>
               <tr>
    		   <?php } ?>
               
                   <td <?php $this->classes('nopmb ctd'); ?> width="50%">
                    <div <?php $this->classes('cdiv'); ?>>
                        <h2 <?php $this->classes('ch2'); ?>>
                            <a <?php $this->classes('clink'); ?> href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
						<?php the_title(); ?>
                                            </a>
                                        </h2>
                                        <small <?php $this->classes('nopmb cdate'); ?>>
                        <?php the_time('F j, Y') ?> | <a href="<?php the_syndication_source_link(); ?>" target="_blank"><?php echo html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8') ?></a>
                                        </small>
                                        <div <?php $this->classes('nopmb extext'); ?>>
                        <?php //$this->the_content( __( '(more...)' ) ); ?>
                        <?php $content = strip_tags(get_the_excerpt());
							  $content = str_replace(array("Read more &#8250;","[...]"),"",$content);
							  echo implode(' ', array_slice(explode(' ', $content), 0, 50)); ?> <a href="<?php the_permalink() ?>">Read more &raquo;</a>
                        </div>
                    </div>
                   </td>

			 <?php  if ($col != "left"){ ?>
               </tr>
    		   <?php } ?>

<?php endif; ?>
<?php endwhile; ?>
<?php  if ($col == "left"){ ?>
 <td></td></tr>
<?php } ?>
<?php if (!$info):?>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?> colspan="2">
			<div <?php $this->classes('cdiv'); ?>>
				<div <?php $this->classes('nopmb'); ?>>
<p <?php $this->classes('nopmb noinfo'); ?>>No new participant posts in this newsletter</p>
				</div>
			</div>
		</td>
	</tr>
<?php endif; ?>
<?php //rewind_posts(); ?>
	<tr>
		<td <?php $this->classes('nopmb csection'); ?> colspan="2">Bookmarks <small>(<a href="<?php echo site_url(); ?>/category/bookmarks">Visit on site</a>)</small></td>
    </tr>

        	
<?php 
$info = false; 
$bookDelicious = ""; 
$bookDiigo = ""; 
query_posts(array_merge( $oldquery, array( 'category_name' => 'bookmarks' ) ));
?>
<?php while (have_posts()) : the_post(); ?>
<?php if (in_category('bookmarks')): ?>
<?php $info = true; ?>
<?php 
 if (in_category('delicious'))
	$bookDelicious .= '<li><a href="'. get_permalink() . '" rel="bookmark" title="Permanent Link to '.get_the_title().'">' . html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8') .'</a></li>';
 if (in_category('diigo'))
	$bookDiigo .= '<li><a href="'. get_permalink() . '" rel="bookmark" title="Permanent Link to '.get_the_title().'">' . html_entity_decode(get_the_title(),ENT_QUOTES,'UTF-8') .'</a></li>';
?>				
			
<?php endif; ?>
<?php endwhile; ?>
<?php if ($bookDelicious || $bookDiigo): ?>
    <tr>
		<td <?php $this->classes('nopmb ctd'); ?> colspan="2">
          <div <?php $this->classes('cdiv'); ?>>
           <?php if ($bookDelicious): ?>
           <strong>Delicious</strong>
			  <ul><?php echo $bookDelicious; ?></ul>
           <?php endif; ?>
           <?php if ($bookDiigo): ?>
           <strong>Diigo</strong>
			  <ul><?php echo $bookDiigo; ?></ul>
           <?php endif; ?>
		   </div>
		</td>
	</tr>
<?php endif; ?>
<?php if (!$info):?>
	<tr>
		<td <?php $this->classes('nopmb ctd'); ?> colspan="2">
			<div <?php $this->classes('cdiv'); ?>>
				<div <?php $this->classes('nopmb'); ?>>
<p <?php $this->classes('nopmb noinfo'); ?>>No new bookmarks in this newsletter</p>
				</div>
			</div>
		</td>
	</tr>
<?php endif; ?>
</table>

<?php 
function get_bbp_query($topics_array){
	//if(function_exists('bp_is_active')):
	$widget_query = new WP_Query( $topics_array );	
			if ( $widget_query->have_posts() ) :  
			$count = 0; ?>

			<ul style="padding-left:0px; list-style:none;">

				<?php while ( $widget_query->have_posts() ) :
					
						if ($count < 5):
							$count++;
							$widget_query->the_post();
							$topic_id    = bbp_get_topic_id( $widget_query->post->ID ); 
							$author_link = bbp_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'both', 'size' => 18 ) ); ?>
		
							<li style="margin-bottom:5px;">
								<a class="bbp-forum-title" href="<?php bbp_topic_permalink( $topic_id ); ?>" title="<?php bbp_topic_title( $topic_id ); ?>"><?php bbp_topic_title( $topic_id ); ?></a> - <?php bbp_topic_last_active_time( $topic_id ); ?>
									<?php printf( _x( 'by %1$s', 'widgets', 'bbpress' ), '<span class="topic-author">' . $author_link . '</span>' ); ?>
							</li>
				  <?php else:
							break;
						endif; ?>
				<?php endwhile; ?>

			</ul>

			<?php 
			//wp_reset_postdata();

		endif;
		//wp_reset_query();
	//endif;
}
function get_bbp_recent_topics(){
	$topics_query = array(
					'author'         => 0,
					'post_type'      => bbp_get_topic_post_type(),
					'post_parent'    => 'any',
					'posts_per_page' => 5,
					'post_status'    => join( ',', array( bbp_get_public_status_id(), bbp_get_closed_status_id() ) ),
					'show_stickes'   => false,
					'order'          => 'DESC',
					'meta_query'     => array( bbp_exclude_forum_ids( 'meta_query' ) )
				);	
	get_bbp_query($topics_query);
}
function get_bbp_recent_replies(){
	$replies_query = array(
					'post_type'      => array( bbp_get_reply_post_type() ),
					'post_status'    => join( ',', array( bbp_get_public_status_id(), bbp_get_closed_status_id() ) ),
					'posts_per_page' => 5,
					'meta_query'     => array( bbp_exclude_forum_ids( 'meta_query' ) )
				) ;
	get_bbp_query($replies_query);
}
?>