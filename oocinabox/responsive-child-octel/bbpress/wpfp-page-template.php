<?php
  echo '<script src="'.get_stylesheet_directory_uri().'/js/readerlite.js"></script>'; 
    //echo "<div class='wpfp-span'>";
    if (!empty($user)):
        if (!wpfp_is_user_favlist_public($user)):
            echo "$user's Favorite Posts.";
        else:
            echo "$user's list is not public.";
        endif;
    endif;

    if ($wpfp_before):
        echo "<p>".$wpfp_before."</p>";
    endif;

    if ($favorite_post_ids):
		$favorite_post_ids = array_reverse($favorite_post_ids);
        $post_per_page = wpfp_get_option("post_per_page");
        $page = intval(get_query_var('paged'));
        query_posts(array('post__in' => $favorite_post_ids, 'posts_per_page'=> $post_per_page, 'orderby' => 'post__in', 'paged' => $page));
		echo '<div id="accordionLoader" class="inifiniteLoader">Loading... </div>';
		echo '<div id="accordion" width="320px">'; 
			get_template_part( 'content-ajaxed' );
		echo '</div>';
		
        echo '<div class="navigation">';
            if(function_exists('wp_pagenavi')) { wp_pagenavi(); } else { ?>
            <div class="alignleft"><?php next_posts_link( __( '&larr; Previous Entries', 'buddypress' ) ) ?></div>
            <div class="alignright"><?php previous_posts_link( __( 'Next Entries &rarr;', 'buddypress' ) ) ?></div>
            <?php }
        echo '</div>';

        wp_reset_query();
    else:
        echo "<ul><li>";
        echo "No favourites";
        echo "</li></ul>";
    endif;
	echo '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
    echo '<p>'.wpfp_clear_list_link().'</p>';
   // echo "</div>";
    //wpfp_cookie_warning();
?>
