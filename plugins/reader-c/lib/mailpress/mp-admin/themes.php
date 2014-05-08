<?php
class MP_AdminPage extends MP_adminpage_
{
	const screen 		= MailPress_page_themes;
	const capability 	= 'MailPress_switch_themes';
	const help_url		= 'http://blog.mailpress.org/tutorials/';
	const file        	= __FILE__;

////  Redirect  ////

	public static function redirect() 
	{
		$th = new MP_Themes();

		if ( isset($_GET['action']) ) 
		{
			check_admin_referer('switch-theme_' . $_GET['stylesheet']);
			if ('activate' == $_GET['action']) 
			{
				$th->switch_theme($_GET['template'], $_GET['stylesheet']);
				self::mp_redirect(MailPress_themes . '&activated=true');
			}
		}
	}

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		$styles[] = 'thickbox';
		parent::print_styles($styles);
	}

////  Scripts  ////

	public static function print_scripts($scripts = array()) 
	{
		wp_register_script( self::screen, 	'/' . MP_PATH . 'mp-admin/js/themes.js', array( 'thickbox', 'jquery' ), false, 1);

		$scripts[] = self::screen;
		parent::print_scripts($scripts);
	}

//// List ////

	public static function get_list($args) 
	{
		extract($args);

		$th = new MP_Themes();

		$themes = $th->themes;

		foreach($themes as $key => $theme)
		{
			if ( 'plaintext' == $theme['Stylesheet']) unset($themes[$key]);
			if ( '_' == $theme['Stylesheet'][0] )     unset($themes[$key]);
		}

		ksort( $themes );

		return array(array_slice( $themes, $start, $_per_page ), count( $themes ), $th);
	}

////  Row  ////

	public static function get_row($theme, $row, $col, $rows)
	{
		$class = array('available-theme');
		if ( $row == 1 ) $class[] = 'top';
		if ( $col == 1 ) $class[] = 'left';
		if ( $row == $rows ) $class[] = 'bottom';
		if ( $col == 3 ) $class[] = 'right';

// url's
		$args = array();
		$args['action'] 		= 'activate';
		$args['template'] 	= $theme['Template'];
		$args['stylesheet'] 	= $theme['Stylesheet'];
		$activate_url = esc_url(self::url( MailPress_themes, $args, 'switch-theme_' . $theme['Stylesheet'] ));

		$args['action'] 		= 'theme-preview';
		$args['preview_iframe']	= 1;
		$args['TB_iframe'] 	= 'true';
		$preview_url =  esc_url(self::url( MP_Action_url, $args));

// titles's
		$activate_title	= esc_attr( sprintf( __('Activate &#8220;%s&#8221;'), $theme['Title'] ) );
		$preview_title	= esc_attr( sprintf( __('Preview of &#8220;%s&#8221;'), $theme['Title'] ) );
// actions
		$actions = array();

		$preview['link1']	= "<a class='thickbox thickbox-preview screenshot' href='$preview_url'>";
		if ( $theme['Screenshot'] ) $preview['link1'] .= "<img src='" . $theme['Theme Root URI'] . '/' . $theme['Stylesheet'] . '/' . $theme['Screenshot'] . "' alt='" . esc_attr($theme['Title']) . "' />";
		$preview['link1']     .= '</a>';

		$activate['link2']	= "<a class='activatelink' href='$activate_url' title='$activate_title'>" . __('Activate') . '</a>';
		$preview['link2']		= "<a class='thickbox thickbox-preview'  href='$preview_url' title='$preview_title'>"  . __('Preview')  . '</a>';
?>
			<td class="<?php echo join(' ', $class); ?>">
				<?php echo $preview['link1']; ?>
				<h3><?php echo esc_attr($theme['Title']); ?></h3>
<?php if ( $theme['Description'] ) : ?>
				<p class='description'><?php echo $theme['Description']; ?></p>
<?php endif; ?>
				<span class='action-links'>
					<?php echo $activate['link2']; ?> | 
					<?php echo $preview['link2']; ?>
				</span>
<?php if ( $theme['Tags'] ) : ?>
				<p><?php _e('Tags:'); ?> <?php echo join(', ', $theme['Tags']); ?></p>
<?php endif; ?>
			</td>
<?php
	}
}