<?php 

require_once('../MP_iframe_.class.php');

class MP_Std extends MP_Iframe_
{
	function init()
	{
	// Set up $_GET for shortcode attributes
		$_GET['css'] = $_GET['js'] = $_GET['jq'] = 1;
		$_GET['txtloading'] = '';
	}

	function print_styles()
	{
		wp_register_style( 'mp_form',    site_url() . '/' . MP_PATH_CONTENT . 'advanced/subscription-form/style.css' );
		wp_enqueue_style(  'mp_form');
	}

	function print_scripts()
	{
		wp_register_script( 'mp_form',	'/' . MP_PATH . 'mp-includes/js/mp_form.js', array('jquery') );
		wp_enqueue_script(  'mp_form');
	}

	function get_header()
	{
		include(MP_ABSPATH . 'mp-includes/html/header.php');
		do_action('admin_print_styles');
?>
	</head>
	<body>
<?php 
	}

	function before()
	{
		echo __CLASS__;
	}

	function after()
	{
	}

	function get_footer()
	{
?>
		<script type='text/javascript'>
			/* <![CDATA[ */
			var MP_Widget = { url: '<?php echo MP_Action_url; ?>'	};
			/* ]]> */
		</script>
<?php
		do_action('admin_print_scripts'); 
?>
	</body>
</html>
<?php
	}
}
new MP_Std();