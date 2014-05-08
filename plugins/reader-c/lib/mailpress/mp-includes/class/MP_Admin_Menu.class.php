<?php
class MP_Admin_Menu
{
	function __construct()
	{
		$menus = array();

		foreach (MailPress::capabilities() as $capability => $datas)
		{
			if (isset($datas['menu']) && $datas['menu'] && current_user_can($capability))
			{
				$datas['capability'] 	= $capability;
				$menus[]			= $datas;
			}
		}
		if (empty($menus)) return;

		uasort($menus, create_function('$a, $b', 'return strcmp($a["menu"], $b["menu"]);'));

		$first = true;
		foreach ($menus as $menu)
		{
			if (!$menu['parent'])
			{
				if ($first)
				{
					$toplevel = $menu['page'];
					add_menu_page('', __('Mails', MP_TXTDOM), $menu['capability'], $menu['page'], $menu['func'], 'div');
				}
				$first = false;
			}

			$parent = ($menu['parent']) ? $menu['parent'] : $toplevel;
			add_submenu_page( $parent, $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['page'], $menu['func']);

			if ($menu['page'] == MailPress_page_mails)
			{
				add_submenu_page($toplevel, __('Add New Mail', MP_TXTDOM), '&#160;' . __('Add New'), 'MailPress_edit_mails', MailPress_page_write, array('MP_AdminPage', 'body'));
			}
		}
	}
}