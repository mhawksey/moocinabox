<?php
class MP_Widget extends WP_Widget
{
	function __construct()
	{
		$widget_ops  = array('classname' => 'widget_mailpress', 'description' => __( 'the mailpress subscription form', MP_TXTDOM));
		$control_ops = array('width' => 400, 'height' => 300);
		parent::__construct('mailpress', 'MailPress', $widget_ops, $control_ops);
	}

	function widget($args, $instance) 
	{
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);

		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . stripslashes($title) . $after_title; }

		$instance['widget_id'] = $args['widget_id'];
		self::widget_form($instance); 

		echo $after_widget;
	}

	function update($new_instance, $old_instance) 
	{
		$instance 			= $old_instance;
		$instance['title'] 	= strip_tags($new_instance['title']);
		$instance['txtbutton'] 	= strip_tags($new_instance['txtbutton']);
		$instance['txtsubmgt'] 	= strip_tags($new_instance['txtsubmgt']);
		if (isset($new_instance['css'])) $instance['css'] = true;
		else unset($instance['css']);
   		if (isset($new_instance['jq']))  $instance['jq']  = true;
		else unset($instance['jq']);
		if (isset($new_instance['js']))  $instance['js']  = true;
		else unset($instance['js']);
		if (isset($new_instance['urlsubmgt'])) $instance['urlsubmgt'] = true;
		else {unset($instance['urlsubmgt']); $instance['txtsubmgt'] = '';}

		$instance = apply_filters('MP_Widget_save_instance',$instance);

		return $instance;
	}

	function form($instance) 
	{
		$instance 	= wp_parse_args( (array) $instance, self::form_defaults() );
 		$title 	= (isset($instance['title'])) ? strip_tags($instance['title']) : '';
		$txtbutton	= strip_tags($instance['txtbutton']);
		$txtsubmgt	= strip_tags($instance['txtsubmgt']);
?>
<p>
	<label for='<?php echo $this->get_field_id('title'); ?>'>
		<?php _e('Title:'); ?> 
		<input type='text' id='<?php echo $this->get_field_id('title'); ?>' name='<?php echo $this->get_field_name('title'); ?>' value="<?php echo esc_attr($title); ?>" class='widefat' />
	</label>
	<br /><br />
	<label for='<?php echo $this->get_field_id('txtbutton'); ?>'>
		<?php _e('Button:'); ?> 
		<input type='text' id='<?php echo $this->get_field_id('txtbutton'); ?>' name='<?php echo $this->get_field_name('txtbutton'); ?>' value="<?php echo esc_attr($txtbutton); ?>" class='widefat' />
	</label>
	<br /><br />
	<label for='<?php echo $this->get_field_id('urlsubmgt'); ?>'>
		<input type='checkbox' id='<?php echo $this->get_field_id('urlsubmgt'); ?>' name='<?php echo $this->get_field_name('urlsubmgt'); ?>'<?php if ($instance['urlsubmgt']) checked(true); ?> onchange="jQuery('#<?php echo $this->get_field_id('txtsubmgt') ; ?>').toggle();" />
		<?php _e("\"Manage your subscription\" link ?", MP_TXTDOM); ?>
	</label>
	<label for='<?php echo $this->get_field_id('txtsubmgt'); ?>'>
		<input type='text' id='<?php echo $this->get_field_id('txtsubmgt'); ?>' name='<?php echo $this->get_field_name('txtsubmgt'); ?>' value="<?php echo esc_attr($txtsubmgt); ?>" class='widefat<?php if (!$instance['urlsubmgt']) echo ' hide-if-js'; ?>' />
	</label>
	<br />
	<div style='background-color:#f1f1f1;border:solid 1px #ddd;color:#999;padding:3px;'>
		<small>
			<?php _e('Preloaded :', MP_TXTDOM); ?>
			<label for='<?php echo $this->get_field_id('css'); ?>'>
				<input type='checkbox' id='<?php echo $this->get_field_id('css'); ?>' name='<?php echo $this->get_field_name('css'); ?>'<?php if ($instance['css']) checked(true); ?> /> <?php _e('css', MP_TXTDOM); ?> 
			</label>
			<label for='<?php echo $this->get_field_id('jq'); ?>'>
				<input type='checkbox' id='<?php echo $this->get_field_id('jq'); ?>' name='<?php echo $this->get_field_name('jq'); ?>'<?php if ($instance['jq']) checked(true); ?> /> <?php _e('jQuery', MP_TXTDOM); ?> 
			</label>
			<label for='<?php echo $this->get_field_id('js'); ?>'>	
				<input type='checkbox' id='<?php echo $this->get_field_id('js'); ?>' name='<?php echo $this->get_field_name('js'); ?>'<?php if ($instance['js']) checked(true); ?> /> <?php _e('javascript', MP_TXTDOM); ?>
		</label>
		</small>
	</div>
</p>
<?php
	}

////  Defaults  ////

	public static function form_defaults($options = array()) 
	{
		$defaults = array(	'css'			=> false,
						'jq'			=> false,
						'js'			=> false,
						'urlsubmgt' 	=> false, 
						'txtbutton' 	=> __('Subscribe', MP_TXTDOM), 
						'txtsubmgt' 	=> __('Manage your subscription', MP_TXTDOM), 
						'txtloading'	=> __('Loading...', MP_TXTDOM), 

						'txtfield' 		=> __('Your email', MP_TXTDOM), 
						'txtfieldname' 	=> __('Your name', MP_TXTDOM), 
						'txtwait'		=> __('Waiting for ...', MP_TXTDOM), 
						'txtwaitconf' 	=> __('Waiting for your confirmation', MP_TXTDOM), 
						'txtallready' 	=> __('You have already subscribed', MP_TXTDOM), 
						'txtvalidemail' 	=> __('Enter a valid email !', MP_TXTDOM), 
						'txterrconf' 	=> __('ERROR. resend confirmation email failed', MP_TXTDOM), 
						'txtdberror' 	=> __('ERROR in the database : subscriber not inserted', MP_TXTDOM)
					);

		$defaults = apply_filters('MailPress_form_defaults', $defaults);
		$options  = wp_parse_args( $options, $defaults );
		$options  = apply_filters('MailPress_form_options', $options);
		return $options;
	}

////  Form  ////

	public static function widget_form($options = array()) 
	{
		static $wp_head = false;

		$email = $name = $message = $widget_title = '';

		$options  = self::form_defaults($options);

		$id = $options['widget_id'];

		if (isset($_POST['MailPress_submit']) && ($_POST['id'] == '_MP_' . $id))
			list($message, $email, $name) = self::insert(false);
		else
		{
			$user = wp_get_current_user();
			switch (true)
			{
				case ( $user->ID ) :
// user connected
					$email = $user->user_email;
					$name  = $user->display_name;
				break;
				default :
// user as already a cookie !
					$email = (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) ? $_COOKIE['comment_author_email_' . COOKIEHASH] : '';
					$name  = (isset($_COOKIE['comment_author_'       . COOKIEHASH])) ? $_COOKIE['comment_author_'       . COOKIEHASH] : '';
				break;
			}
			$email = apply_filters('MailPress_form_email', $email);
			$name  = apply_filters('MailPress_form_name',  $name);

			if ( MP_User::is_user($email) ) $email = $name = ''; 
		}
		if ('' == $email) $email = $options['txtfield'];
		if ('' == $name)  $name  = $options['txtfieldname'];
?>

<!-- start of code generated by MailPress (<?php echo MP_Version; ?>) -->
<?php
		if (!$wp_head)
		{
			$wp_head = true;
			if (!$options['css']) 
			{
				$root = MP_CONTENT_DIR . 'advanced/subscription-form';
				$root = apply_filters('MailPress_advanced_subscription-form_root', $root);
				echo "<style  type='text/css'>\n"; include "$root/style.css"; echo "\n</style>\n"; 
			}
			if (!$options['jq'])
			{
				if (defined('MP_wp_enqueue_script') && MP_wp_enqueue_script)
				{
					wp_enqueue_script('jquery');
				}
				else
				{
					echo "<script type='text/javascript' src='" . site_url() . "/wp-includes/js/jquery/jquery.js'></script>\n";
				}
			}
			if (!$options['js'])
			{
				$js = '/' . MP_PATH . 'mp-includes/js/mp_form.js';
				if (defined('MP_wp_enqueue_script') && MP_wp_enqueue_script)
				{
					wp_register_script( 'mailpress_widget',	$js);
					wp_localize_script( 'mailpress_widget', 	'MP_Widget', array(
						'url' => MP_Action_url
					));
					wp_enqueue_script('mailpress_widget');
				}
				else
				{
					echo "<script type='text/javascript'>\n/* <![CDATA[ */\nvar MP_Widget = {\n\turl: '" . MP_Action_url . "'\n};\n/* ]]> */\n</script>\n";
					echo "<script type='text/javascript' src='" . site_url() . $js . "'></script>\n";
				}
			}
		}

		$imgloading = apply_filters('MailPress_form_imgloading', site_url() . '/' . MP_PATH . 'mp-includes/images/loading.gif');
?>
<div class='MailPress' id='_MP_<?php echo $id; ?>'>
	<div class='mp-container'>
		<div class='mp-message'></div>
		<div class='mp-loading'><img src='<?php echo $imgloading; ?>' alt='<?php  echo $options['txtloading']; ?>' title='<?php  echo $options['txtloading']; ?>' /><?php  echo $options['txtloading']; ?></div>
		<div class='mp-formdiv'>
<?php if ('' != $message) echo $message . "\n"; ?>
			<form class='mp-form' method='post' action=''>
				<input type='hidden' name='action' 	value='add_user_fo' />
				<input type='hidden' name='id' 	value='<?php echo '_MP_' . $id; ?>' />
				<input type='text'   name='_MP_email'  	value="<?php echo $email; ?>" class='MailPressFormEmail' size='25' onfocus="if(this.value=='<?php echo esc_js($options['txtfield']); ?>') this.value='';" onblur="if(this.value=='') this.value='<?php echo esc_js($email); ?>';" /><br />
				<input type='text'   name='_MP_name'  	value="<?php echo $name;  ?>" class='MailPressFormName'  size='25' onfocus="if(this.value=='<?php echo esc_js($options['txtfieldname']); ?>') this.value='';" onblur="if(this.value=='') this.value='<?php echo esc_js($name); ?>';" /><br />
<?php do_action('MailPress_form', $email, $options); ?>
				<input class='MailPressFormSubmit mp_submit' type='submit' name='MailPress_submit' value="<?php echo esc_attr($options['txtbutton']); ?>" />
			</form>
		</div>
	</div>
<?php 
$url = ($options['urlsubmgt']) ? esc_url(MP_WP_User::get_unsubscribe_url()) : false;
if ($url) :
?>
	<div id='mp-urlsubmgt'><a href='<?php echo $url; ?>'><?php echo (!empty($options['txtsubmgt'])) ? esc_attr($options['txtsubmgt']) : __('Manage your subscription', MP_TXTDOM); ?></a></div>
<?php
endif;
?>
<?php do_action('MailPress_form_div_misc', $email, $options); ?>
</div>
<!-- end of code generated by MailPress (<?php echo MP_Version; ?>) -->
<?php
	}

	public static function insert($ajax = true)
	{
		$bots_useragent = array('googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'ubsub', 'syndic8', 'userland', 'gigabot', 'become.com');
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		foreach ($bots_useragent as $bot) if (stristr($useragent, $bot) !== false) return false;				// goodbye bot !

		$message = $email = $name = '';
		$options = self::form_defaults();

		$email = ( isset($_POST['_MP_email']) ) ? $_POST['_MP_email'] : '';									//has the user entered an email 
		$name  = ( isset($_POST['_MP_name']) )  ? $_POST['_MP_name']  : '';	

		if ( '' == $email || $options['txtfield'] == $email ) 
		{																		// check for bot
			$message = "<span class='error'>" . $options['txtwait'] . "</span>";
			$email = $options['txtfield'];
		}
		else
		{
			$name = ( '' == $name || $options['txtfieldname'] == $name ) ? '' : $name;
			$add = MP_User::add($email, $name);
			$shortcode_message = apply_filters('MailPress_form_submit', '', $email);
			$message = ($add['result']) ? "<span class='success'>" . $add['message'] . $shortcode_message . "</span><br />" : "<span class='error'>" . $add['message']  . $shortcode_message . "</span><br />";
			$email   = ($add['result']) ? $email : $options['txtfield'];
			$name    = ($add['result']) ? $name  : $options['txtfieldname'];
			if ($add['result']) 
				if ($ajax) 	do_action('MailPress_form_user_added_ajax', $email, $name, $options);
				else 		do_action('MailPress_form_user_added', $email, $name, $options);
		}

		$user = wp_get_current_user();
		if ( !$user->ID ) 
		{
			$comment_cookie_lifetime = apply_filters('comment_cookie_lifetime', 30000000);
			setcookie('comment_author_'       . COOKIEHASH, $name,  time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('comment_author_email_' . COOKIEHASH, $email, time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
		}

		return array($message, $email, $name);
	}
}