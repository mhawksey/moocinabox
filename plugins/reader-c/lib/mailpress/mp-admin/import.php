<?php
class MP_AdminPage extends MP_adminpage_list_
{
	const screen 		= MailPress_page_import;
	const capability 	= 'MailPress_import';
	const help_url		= 'http://blog.mailpress.org/tutorials/add-ons/import/';
	const file        	= __FILE__;

	const per_page 		= false;

////  Title  ////

	public static function title() 
	{
		new MP_Import_importers();
	}

////  Columns  ////

	public static function get_columns() 
	{
		$columns = array(	'name' 	=> __('Name', MP_TXTDOM), 
					'desc'	=> __('Description', MP_TXTDOM));
		return $columns;
	}

//// List ////

	public static function get_list($args = array()) 
	{
		$importers = MP_Import_importers::get_all();

		return ( empty($importers) ) ? false : $importers;
	}

////  Row  ////

	public static function get_row( $id, $data ) 
	{

		static $row_class = '';

// url's
		$url_parms = array();
		$url_parms['mp_import'] 	= $id;
		$import_url = esc_url(self::url( MailPress_import, $url_parms ));
// actions
		$actions = array();
		$actions['import'] = "<a href='$import_url' title='" . wptexturize(strip_tags($data[1])) . "'>" . ((strpos($id, 'export') !== false) ?  __('Export', MP_TXTDOM) : __('Import', MP_TXTDOM) ) . '</a>';

		$row_class = 'alternate active' == $row_class ? '' : 'alternate active';

		$out = '';
		$out .= "<tr class='$row_class'>";

		$columns = self::get_columns();
		$hidden  = self::get_hidden_columns();

		foreach ( $columns as $column_name => $column_display_name ) 
		{
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array($column_name, $hidden) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			switch ($column_name) 
			{
				case 'name':
					$out .= "<td $attributes><strong><a class='row-title' href='$import_url' title='" . esc_attr(sprintf(__('Import "%s"', MP_TXTDOM), $data[1])) . "'>{$data[0]}</a></strong>";
					$out .= self::get_actions($actions);
					$out .= '</td>';
				break;
				case 'desc' :
					$out .= "<td $attributes>" . $data[1] . "</td>";
				break;
			}
		}
		$out .= '</tr>';

		return $out;
	}
}