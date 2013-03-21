<?php
/**
 * Add theme support for infinity scroll
 */
function infinite_scroll_init() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'accordion',
		'render'    => 'infinite_scroll_render',
		'wrapper'   => false,
		'footer'    => false
	) );
}
//
if (!is_admin() && $_GET['action'] == "profile"){
	get_currentuserinfo();
	//$location("/forums");
	//wp_redirect( $location);
	//exit;
}
//echo "<pre>"; print_r($_GET['action']); echo "</pre>";

add_filter('user_contactmethods','add_hide_profile_fields',10,1);

function add_hide_profile_fields( $contactmethods ) {
unset($contactmethods['aim']);
unset($contactmethods['jabber']);
unset($contactmethods['yim']);
// Add Twitter
$contactmethods['twitter'] = 'Twitter';
//add Facebook
$contactmethods['facebook'] = 'Facebook';
$contactmethods['googleplus'] = 'Google+';

return $contactmethods;
}


add_action( 'init', 'infinite_scroll_init' );


/**
 * Set the code to be rendered on for calling posts,
 * hooked to template parts when possible.
 *
 * Note: must define a loop.
 */
function infinite_scroll_render() {
	get_template_part( 'content' );
}

add_action( 'wp_footer', 'infiniteFooter' );

add_action('wp_ajax_ajaxify', 'ajaxify');           // for logged in user  
add_action('wp_ajax_nopriv_ajaxify', 'ajaxify'); 
add_action( 'wp_head', 'infintieHeader' );
add_action('wp_enqueue_scripts', 'my_scripts_method');

function my_scripts_method() {
if ( !is_admin() ) {
	wp_enqueue_script('jquery-ui-accordion'); 
    }
}
// record if post has been read in the reader view
include("include/readerlite_mark_post_as_read.php");
if (get_option('readerlite_mark_as_read') != 'true'){
	include("installation.php");
	readerlite_mar_install();
	add_option('readerlite_mark_as_read','true');
}

function ajaxify() { // loads post content into accordion
    $post_id = $_POST['post_id'];
	query_posts(array('p' => $post_id, 'post_type' => array('post')));
	ob_start();
	while (have_posts()) : the_post(); 
	$source = html_entity_decode(get_syndication_source(),ENT_QUOTES,'UTF-8');
	$posturl = get_permalink($post_id);
	$posturlen = urlencode($posturl);
	$title = html_entity_decode(get_the_title($post_id),ENT_QUOTES,'UTF-8');
	$titleen = rawurlencode($title);
	$buttons = '<div class="share_widget post-'.$post_id.'">Share this post on: ' 
		. '<div class="gplusbut"><g:plusone size="small" annotation="none" href="'.$posturl.'"></g:plusone><script type="text/javascript">  window.___gcfg = {lang: "en-GB"};  (function() { var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;    po.src = "https://apis.google.com/js/plusone.js";    var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);  })();</script></div><span class="share-count"><i></i><u></u><span id="gp-count">-</span></span>'
		. ' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.facebook.com/sharer.php?u='.$posturlen.'\')" >Facebook</a><span class="share-count"><i></i><u></u><span id="fb-count">-</span></span>'
		.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://twitter.com/intent/tweet?text='.$titleen.'%20'.$posturlen.'\')">Twitter</a><span class="share-count"><i></i><u></u><span id="tw-count">-</span></span>'
		.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://www.linkedin.com/shareArticle?mini=true&url='.$posturlen.'&source=MASHe&summary='.$titleen.'\')" >LinkedIn</a><span class="share-count"><i></i><u></u><span id="li-count">-</span></span>'
		.' | <a href="javascript:void(0);" onclick="pop(\'ShareWin\',\'https://delicious.com/post?v=4&url='.$posturlen.'\')" >Delicious</a><span class="share-count"><i></i><u></u><span id="del-count">-</span></span>';
	 ?>
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
       <?php echo $buttons; ?>
       <?php
	endwhile;
	$output = ob_get_contents();
	ob_end_clean();
	if(function_exists('readerlite_mark_post_as_read')){
    	readerlite_mark_post_as_read();
	}
	echo $output;

	die(1);
}
function infiniteFooter(){ 
?>
	<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
    <script type="text/javascript">function pop(title,url,optH,optW){ // script to handle social share popup
	h = optH || 500;
	w = optW || 680;
	sh = window.innerHeight || document.body.clientHeight;
	sw = window.innerWidth || document.body.clientWidth;
	wd = window.open(url, title,'scrollbars=no,menubar=no,height='+h+',width='+w+',resizable=yes,toolbar=no,location=no,status=no,top='+((sh/2)-(h/2))+',left='+((sw/2)-(w/2)));
}</script>
<?php
}

function infintieHeader(){
global $wp_query;
if (!is_single() || !is_page()): 
   echo '<script src="'.get_stylesheet_directory_uri().'/js/readerlite.js"></script>';
?>
<script type="text/javascript">
jQuery(document).ready(function($) {

	$( "#accordion" ).accordion({active: false, collapsible: true, heightStyle: "content"});
	customAccordionHooks();	
	$( "#accordion" ).show();
	$( "#accordionLoader" ).hide();
	
	// shared count getter https://gist.github.com/yahelc/1413508#file-jquery-sharedcount-js
	
	$( document.body ).on( 'post-load', function(){
		$('.infinite-loader').remove();
		var opened = $("#accordion").accordion( "option", "active" );
		$("#accordion").accordion('destroy');
		$("#accordion").accordion({active: opened, collapsible: true, heightStyle: "content"});
		customAccordionHooks();	
	});

	$("#accordion").on("click", ".ajaxed", function(event){
		event.preventDefault(); 
		var post_id = $(this).attr("id");
		var post_url  = $(this).attr("url");
		// clean post url removing GA utm_ for shared count
		post_url = post_url.replace(/\?([^#]*)/, function(_, search) {
						search = search.split('&').map(function(v) {
						  return !/^utm_/.test(v) && v;
						}).filter(Boolean).join('&'); // omg filter(Boolean) so dope.
						return search ? '?' + search : '';
						});
		$.ajax({
			type: 'POST',
			url: "<?php bloginfo('wpurl') ?>/wp-admin/admin-ajax.php",
			data: ({
				action : 'ajaxify',
				post_id: post_id
				}),
			success:function(response){
				$("#post-"+post_id).html(response);
				twttr.widgets.load();
				$("#accordion").accordion("refresh");
				$("#accordion h3[aria-controls='post-"+post_id+"']").addClass("read");
				// added sharedcount.com data to accordion foot
				$.sharedCount(post_url, function(data){
						$("#post-"+post_id+" span#tw-count").text(data.Twitter);
						$("#post-"+post_id+" span#fb-count").text(data.Facebook.like_count);
						$("#post-"+post_id+" span#gp-count").text(data.GooglePlusOne);
						$("#post-"+post_id+" span#li-count").text(data.LinkedIn);
						$("#post-"+post_id+" span#del-count").text(data.Delicious);
				});
				
			}
		});
	});
});

</script>
<!-- end infinite scroll pagination -->
<?php endif; ?>	
<?php
}
?>