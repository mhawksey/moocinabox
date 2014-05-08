<?php
class MP_Mailinglist
{
	const taxonomy 	= MailPress_mailinglist::taxonomy;

	public static function exists($term_name) 
	{
		$id = term_exists($term_name, self::taxonomy);
		if ( is_array($id) )	$id = $id['term_id'];
		return $id;
	}

	public static function get($term_id, $output = OBJECT, $filter = 'raw') 
	{
		$term = get_term($term_id, self::taxonomy, $output, $filter);
		if ( is_wp_error( $term ) )	return false;
		$term->slug = self::remove_slug($term->slug);
		return $term;
	}

	public static function get_id($name = 'General') 
	{
		$term = get_term_by('name', $name, self::taxonomy);
		if ($term) return $term->term_id;
		return 0;
	}

	public static function get_name($term_id) 
	{
		$term_id = (int) $term_id;
		$term = self::get($term_id);
		return ($term) ? $term->name : false;
	}

	public static function create( $term_name, $parent = 0 ) 
	{
		if ( $id = self::exists($term_name) ) return $id;
		return self::insert( array('name' => trim($term_name), 'parent' => $parent) );
	}

	public static function insert($term_arr, $wp_error = false) 
	{
		$term_defaults = array('id' => 0, 'name' => '', 'slug' => '', 'parent' => 0, 'description' => '');
		$term_arr = wp_parse_args($term_arr, $term_defaults);
		extract($term_arr, EXTR_SKIP);

		if ( trim( $name ) == '' ) 
		{
			if ( ! $wp_error )	return 0;
			else				return new WP_Error( 'mailinglist_name', __('You did not enter a mailing list name.', MP_TXTDOM) );
		}

		$slug = self::add_slug($slug, $name);
		$description = mysql_real_escape_string($description);

		$id = (int) $id;

		// Are we updating or creating?
		$update = (!empty ($id) ) ? true : false;

		// hierarchy !
		$parent 		= (int) $parent;
		if ( $parent < 0 ) 						$parent = 0;
		elseif ( empty($parent) )					$parent = 0;
		elseif (!self::exists( $parent )) 				$parent = 0;
 		elseif ($id && self::is_ancestor_of($id, $parent))	$parent = 0;

		$args = compact('name', 'slug', 'parent', 'description');

		if ( $update )	$term = wp_update_term($id,   self::taxonomy, $args);
		else			$term = wp_insert_term($name, self::taxonomy, $args);

		if ( is_wp_error($term) ) 
		{
			if ( $wp_error )	return $term;
			else			return 0;
		}

		return $term['term_id'];
	}

	public static function delete($term_id) 
	{
		$default 		= get_option(MailPress_mailinglist::option_name_default);
		if ( $term_id == $default ) return false;

		global $mp_subscriptions;
		unset( $mp_subscriptions['display_mailinglists'][$term_id] );
		update_option (MailPress::option_name_subscriptions, $mp_subscriptions );

		return wp_delete_term( $term_id, self::taxonomy, array('default' => $default) );
	}

	public static function get_all($args = '')
	{
		$defaults = array();
		$args     = wp_parse_args($args, $defaults);
		$terms    = get_terms(self::taxonomy, $args);
		if (empty($terms)) return array();
		foreach ($terms as $k => $term) 
		{
			$terms[$k]->slug = self::remove_slug($terms[$k]->slug);
		}
		return $terms;
	}

	public static function get_all_ids() 
	{
		$term_ids = get_terms(self::taxonomy, 'fields=ids&get=all');
		return $term_ids;
	}

////  Hierarchy  ////

	public static function is_ancestor_of($parent, $child) 
	{
		if ( is_int($parent) )	$parent = self::get($parent);
		if ( is_int($child) )	$child  = self::get($child);

		if ( !$parent->term_id || !$child->parent )	return false;

		if ( $child->parent == $parent->term_id )		return true;

		return self::is_ancestor_of($parent, self::get($child->parent));
	}

	public static function get_children($id, $before = '/', $after = '', $visited = array()) 
	{
		if ( 0 == $id )	return '';

		$chain = '';
		$term_ids = self::get_all_ids();
		foreach ( $term_ids as $term_id ) 
		{
			if ( $term_id == $id )	continue;

			$term = self::get($term_id);

			if ( is_wp_error( $term ) )	return $term;

			if ( $term->parent == $id && !in_array($term->term_id, $visited) ) 
			{
				$visited[] 	 = $term->term_id;
				$chain 	.= $before.$term->term_id.$after;
				$chain 	.= self::get_children($term->term_id, $before, $after);
			}
		}
		return $chain;
	}

////  Object  ////

	public static function get_object_terms( $object_id = 0, $args = array() ) 
	{
		$object_id = (int) $object_id;

		$defaults = array('fields' => 'ids');
		$args = wp_parse_args( $args, $defaults );
	
		$terms = wp_get_object_terms($object_id, self::taxonomy, $args);
		return $terms;
	}

	public static function set_object_terms( $object_id, $object_terms = array() )
	{
		$object_id = (int) $object_id;
		if (!is_array($object_terms) || 0 == count($object_terms) || empty($object_terms)) $object_terms = array(apply_filters('MailPress_mailinglist_default', get_option(MailPress_mailinglist::option_name_default)));
		else if ( 1 == count($object_terms) && '' == $object_terms[0] ) return true;

		$object_terms = array_map('intval', $object_terms);
		$object_terms = array_unique($object_terms);
		if (!is_array($object_terms)) $object_terms = array();

		$in = self::get_object_terms($object_id);
		if (!is_array($in)) $in = array();

		$added = array_diff_assoc($object_terms, $in);
		foreach ($added as $term_id) do_action('MailPress_mailinglist_new_subscriber', array('mp_user_id' => $object_id, 'mailinglist_id' => $term_id));
		$removed = array_diff_assoc($in, $object_terms);
		foreach ($removed as $term_id) do_action('MailPress_mailinglist_new_unsubscriber', array('mp_user_id' => $object_id, 'mailinglist_id' => $term_id));
		
		return wp_set_object_terms($object_id, $object_terms, self::taxonomy);
	}

	public static function delete_object( $object_id )
	{
		wp_delete_object_term_relationships($object_id, array(self::taxonomy));
	}

////  Layouts  ////

	public static function dropdown($args = '') 
	{
		$defaults = array('child_of' 		=> 0, 
					'class'		=> 'postform', 
					'depth' 		=> 0, 
					'echo' 		=> 1, 
					'exclude' 		=> '', 
					'hide_empty' 	=> 1, 
					'hierarchical'	=> 0, 
					'htmlid'		=> 'mailinglist_dropdown', 
					'name' 		=> 'mailinglist', 
					'order' 		=> 'ASC', 
					'orderby' 		=> 'ID', 
					'selected' 		=> 0, 
					'show_count' 	=> 0, 
					'show_last_update'=> 0, 
					'show_option_all' => '', 
					'show_option_none'=> '', 
					'tab_index' 	=> 0
					);

		$r = wp_parse_args( $args, $defaults );
		$r['include_last_update_time'] = $r['show_last_update'];
		extract( $r );

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )	$tab_index_attribute = " tabindex=\"$tab_index\"";

		$mailinglists = self::get_all($r);

		$output = '';
		if ( ! empty($mailinglists) ) 
		{
			$htmlid = ($htmlid === true) ? "id='$name'" : "id='$htmlid'" ;
			$output = "<select name='$name' $htmlid class='$class' $tab_index_attribute>\n";

			if ( $show_option_all ) 
				$output .= "\t<option value='0'>$show_option_all</option>\n";

			if ( $show_option_none) 
				$output .= "\t<option value='-1'>$show_option_none</option>\n";

			if ( $hierarchical )	$depth = $r['depth'];  		// Walk the full depth.
			else				$depth = -1; 			// Flat.

			$output .= self::walk_dropdown_tree($mailinglists, $depth, $r);
			$output .= "</select>\n";
		}

		if ( $echo )	echo $output;

		return $output;
	}

	public static function walk_dropdown_tree() 
	{
		$walker = new MP_Mailinglists_Walker_Dropdown;
		$args = func_get_args();
		return call_user_func_array(array(&$walker, 'walk'), $args);
	}

	public static function array_tree($args = '') 
	{
		$defaults = array('child_of' 		=> 0,
					'class'		=> 'postform',
					'depth' 		=> 0,
					'exclude' 		=> '',
					'hide_empty' 	=> 1,
					'hierarchical'	=> 0,	
					'name' 		=> 'mailinglist',
					'order' 		=> 'ASC',
					'orderby' 		=> 'ID',
					'selected' 		=> 0,
					'show_count' 	=> 0,
					'show_last_update'=> 0, 
					'show_option_all' => '', 
					'show_option_none'=> '',
					'tab_index' 	=> 0
					);

		$r = wp_parse_args( $args, $defaults );
		$r['include_last_update_time'] = $r['show_last_update'];
		extract( $r );

		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )	$tab_index_attribute = " tabindex=\"$tab_index\"";

		$mailinglists = self::get_all($r);

		$output = array();
		if ( ! empty($mailinglists) ) 
		{
			if ( $show_option_all ) 
				$output [0] = $show_option_all;

			if ( $show_option_none) 
				$output [-1] = $show_option_none;

			if ( $hierarchical )		$depth = $r['depth'];  			// Walk the full depth.
			else					$depth = -1; 				// Flat.

			$output = array_merge(self::walk_array($mailinglists, $depth, $r),$output );
		}

		return $output;
	}

	public static function walk_array() 
	{
		$walker = new MP_Mailinglists_Walker_Array;
		$args = func_get_args();
		return call_user_func_array(array(&$walker, 'walk'), $args);
	}

////  Slug  ////

	public static function add_slug( $slug, $name = false )
	{
		$slugs = ($name) ? array($slug, $name) : array($slug);
		foreach ($slugs as $slug)
		{
			$slug = self::remove_slug($slug);
			$slug = trim(stripslashes($slug));
			$slug = str_replace('"', '_', $slug);
			$slug = str_replace("'", '_', $slug);
			if (!empty($slug)) break;
		}
		return $slug = '_' . self::taxonomy . '_' . $slug;
	}

	public static function remove_slug( $slug )
	{
		return str_ireplace('_' . self::taxonomy . '_', '', $slug);
	}
}