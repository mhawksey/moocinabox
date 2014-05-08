<?php
class MP_Admin_Bar_Menu
{
	function __construct($wp_admin_bar)
	{
		$menus = $actions = array();
		foreach (MailPress::capabilities() as $capability => $datas) if (isset($datas['menu'], $datas['admin_bar']) && $datas['menu'] && $datas['admin_bar'] && current_user_can($capability)) $menus[$capability] = $datas;
		if (!$menus) return;
		uasort($menus, create_function('$a, $b', 'return strcmp($a["menu"], $b["menu"]);'));

		foreach($menus as $cap => $menu)
		{
			if (!$menu['parent']) $menu['parent'] = 'admin.php';
			if ($menu['page'] == MailPress_page_mails)  $actions['admin.php?page=' . MailPress_page_write] = array(__('Mail'), $cap . '_write');
		}

		foreach ( $actions as $link => $action ) {
			list( $title, $id ) = $action;
			$secondary = !empty( $action[2] );
	
			$wp_admin_bar->add_menu( array(
				'parent'    => 'new-content',
				'secondary' => $secondary,
				'id'        => $id,
				'title'     => $title,
				'href'      => admin_url( $link )
			) );
		}
	}

}