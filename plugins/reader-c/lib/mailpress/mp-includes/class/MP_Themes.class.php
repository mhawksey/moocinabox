<?php
class MP_Themes
{
	const default_theme_folder 	= 'twentyten';
	const default_theme_name 	= 'MailPress Twenty Ten';

	const option_current_theme 	= 'MailPress_current_theme';
	const option_stylesheet		= 'MailPress_stylesheet';
	const option_template		= 'MailPress_template';

	function __construct()
	{
		global $mp_theme_directories;

		$mp_theme_directories 		= $this->get_theme_directories();

		$this->themes			= $this->get_themes();
		$this->current_theme 		= $this->get_current_theme();
		$this->path_current_theme 	= ABSPATH . $this->themes[$this->current_theme] ['Template Dir'];
	}

	function get_folder($folder) 
	{
		return apply_filters("MailPress_{$folder}", get_option(constant("self::option_{$folder}")));
	}

	function get_folder_directory($folder) 
	{
		$func		= "get_{$folder}";
		$folder	= $this->$func();
		$theme_root = $this->get_theme_root( $folder );

		return apply_filters("MailPress_{$folder}_directory", "$theme_root/$folder", $folder, $theme_root );
	}

	function get_theme_by_folder($name, $type = 'Stylesheet') 
	{
		foreach ($this->themes as $theme) if ( $theme[$type] == $name) return $theme;
		return null;
	}

	function get_theme_by_stylesheet($stylesheet) { return $this->get_theme_by_folder($stylesheet, 'Stylesheet'); }

	function get_theme_by_template($template)     { return $this->get_theme_by_folder($template,   'Template'); }

	function get_template_files($path, $t_files = array()) 
	{
		if (is_dir($path)) {
			$dir = @ dir($path);
			if ( $dir ) {
				while ( ($file = $dir->read()) !== false ) {
					if ( preg_match('|^\.+$|', $file) )
						continue;
					if ( preg_match('|\.php$|', $file) ) 
						$t_files[] = str_replace(ABSPATH, '', "$path/$file");
				}
				@ $dir->close();
			}
		}
		return array_unique($t_files);
	}

/* /wp-admin/includes/theme.php */

	function current_theme_info() 
	{
		$themes = $this->themes;
		$current_theme = $this->current_theme;

		if (! $themes) {
			$ct = new stdClass;
			$ct->name = $this->current_theme;
			return $ct;
		}

		if ( ! isset( $themes[$current_theme] ) ) {
			delete_option(self::option_current_theme);
			$current_theme = $this->get_current_theme();
		}

		$ct 				= new stdClass();
		$ct->name 			= $current_theme;
		$ct->title 			= $themes[$current_theme]['Title'];
		$ct->version 		= $themes[$current_theme]['Version'];
		$ct->parent_theme 	= $themes[$current_theme]['Parent Theme'];
		$ct->template_dir 	= $themes[$current_theme]['Template Dir'];
		$ct->stylesheet_dir 	= $themes[$current_theme]['Stylesheet Dir'];
		$ct->template 		= $themes[$current_theme]['Template'];
		$ct->stylesheet 		= $themes[$current_theme]['Stylesheet'];
		$ct->screenshot 		= $themes[$current_theme]['Screenshot'];
		$ct->description 		= $themes[$current_theme]['Description'];
		$ct->author 		= $themes[$current_theme]['Author'];
		$ct->tags 			= $themes[$current_theme]['Tags'];
		$ct->theme_root 		= $themes[$current_theme]['Theme Root'];
		$ct->theme_root_uri 	= $themes[$current_theme]['Theme Root URI'];
		return $ct;
	}

	function get_broken_themes() 
	{
		global $mp_broken_themes;
		return $mp_broken_themes;
	}

	function get_page_templates($t = false, $plaintext = false) 
	{
		$s_dir	= ($plaintext) ? 'Plaintext Stylesheet Dir' : 'Stylesheet Dir';
		$t_dir 	= ($plaintext) ? 'Plaintext Template Dir'   : 'Template Dir';
		$t_files 	= ($plaintext) ? 'Plaintext Template Files' : 'Template Files';

		$themes 	= $this->themes;
		$theme 	= ($t) ? $this->get_theme_by_stylesheet($t) : $themes[$this->current_theme];
		$templates 	= $theme[$t_files];
		$pt 		= array ();

		$stylesheet_directory 	= $theme[$s_dir];
		$template_directory 	= $theme[$t_dir];
		$plaintext_directory 	= ($plaintext && isset($themes['plaintext']['Stylesheet Dir'])) ? $themes['plaintext']['Stylesheet Dir'] : null;

		if ( is_array( $templates ) ) {

			$base[] = $stylesheet_directory;
			$base[] = $template_directory;
			if (isset($plaintext_directory)) $base[] = $plaintext_directory;
			$base = array_map('trailingslashit', array_unique(array_diff($base, array('/'))));

			foreach ( $templates as $template ) {
				$basename = str_replace($base, '', $template);

				// don't allow template files in subdirectories
				if ( false !== strpos($basename, '/') )
					continue;

				if ( 'functions.php' == $basename )
					continue;

				$template_data = implode( '', file( ABSPATH . $template ));

				$name = '';
				preg_match( '|Template Name:(.*)$|mi', $template_data, $name );
				if (empty($name)) continue;
				if (!isset($pt[trim( $name[1] )]['file'])) 	$pt[trim( $name[1] )]['file'] = $basename;

				$subject = '';
				preg_match( '|Subject:(.*)$|mi', $template_data, $subject );
				if (empty($subject)) continue;
				if (!isset($pt[trim( $name[1] )]['subject'])) 	$pt[trim( $name[1] )]['subject'] = $subject[1];
			}
		}
		return $pt;
	}

/* /wp-includes/theme.php */

	function is_child_theme()
	{
		return ( $this->get_template_directory() !== $this->get_stylesheet_directory() );
	}

	function get_stylesheet() { return $this->get_folder('stylesheet'); }

	function get_stylesheet_directory() { return $this->get_folder_directory('stylesheet'); }

	function get_template() { return $this->get_folder('template'); }

	function get_template_directory() { return $this->get_folder_directory('template'); }

	function get_themes() 
	{
		global $mp_themes, $mp_broken_themes;

		if ( isset($mp_themes) ) 
			return $mp_themes;

		if ( !$theme_files = $this->search_theme_directories() )
			return false;

		asort($theme_files);

		$mp_themes = array();

		foreach ( (array) $theme_files as $stylesheet => $theme_file ) {
			$theme_root = $theme_file['theme_root'];
			$theme_file = $theme_file['theme_file'];

			if ( !is_readable("$theme_root/$theme_file") ) {
				$mp_broken_themes[$theme_file] = array('Name' => $theme_file, 'Title' => $theme_file, 'Description' => __('File not readable.'), 'Folder' => $theme_file);
				continue;
			}

			//$theme_data = get_theme_data("$theme_root/$theme_file");
			$theme_data = wp_get_theme($stylesheet, $theme_root);

			$name		= $theme_data['Name'];
			$title	= $theme_data['Title'];
			$description= wptexturize($theme_data['Description']);
			$version	= $theme_data['Version'];
			$author	= $theme_data['Author'];
			$template	= $theme_data['Template'];
			$stylesheet = dirname($theme_file);

			$screenshot = false;
			foreach ( MP_::ext_image() as $ext ) {
				if (is_file("$theme_root/$stylesheet/screenshot.$ext")) {
					$screenshot = "screenshot.$ext";
					break;
				}
			}

			if ( empty($name) ) {
				$name = dirname($theme_file);
				$title = $name;
			}

			$parent_template = $template;

			if ( empty($template) ) {
				if ( is_file("$theme_root/$stylesheet/index.php") )
					$template = $stylesheet;
				else
					continue;
			}

			$template = trim($template);

			if ( !is_file("$theme_root/$template/index.php") ) {
				$parent_dir = dirname(dirname($theme_file));
				if ( is_file("$theme_root/$parent_dir/$template/index.php") ) {
					$template = "$parent_dir/$template";
					$template_directory = "$theme_root/$template";
				} else {
					/**
					* The parent theme doesn't exist in the current theme's folder or sub folder
					* so lets use the theme root for the parent template.
					*/
					if ( isset($theme_files[$template]) && is_file( $theme_files[$template]['theme_root'] . "/$template/index.php" ) ) {
						$template_directory = $theme_files[$template]['theme_root'] . "/$template";
					} else {
						if ( empty( $parent_template) )
							$mp_broken_themes[$name] = array('Name' => $name, 'Title' => $title, 'Description' => __('Template is missing.'), 'error' => 'no_template', 'Folder' => $stylesheet);
						else
							$mp_broken_themes[$name] = array('Name' => $name, 'Title' => $title, 'Description' => sprintf( __('The parent theme is missing. Please install the "%s" parent theme.'), $parent_template ), 'error' => 'no_parent', 'parent' => $parent_template, 'Folder' => $stylesheet );
						continue;
					}
				}
			} else {
				$template_directory = trim( $theme_root . '/' . $template );
			}

			$stylesheet_files = $template_files = array();

			$stylesheet_dir = @ dir("$theme_root/$stylesheet");
			if ( $stylesheet_dir ) {
				while ( ($file = $stylesheet_dir->read()) !== false ) {
					if ( !preg_match('|^\.+$|', $file) ) {
						if ( preg_match('|\.css$|', $file) )
							$stylesheet_files[] = str_replace(ABSPATH, '', "$theme_root/$stylesheet/$file");
						elseif ( preg_match('|\.php$|', $file) )
							$template_files[] = str_replace(ABSPATH, '', "$theme_root/$stylesheet/$file");
					}
				}
				@ $stylesheet_dir->close();
			}
			$stylesheet_files = array_unique($stylesheet_files);
			$stylesheet_dir   = (empty($stylesheet_files )) ? '/' : $theme_root . '/' . $stylesheet;

			$plaintext_stylesheet_files = $this->get_template_files("$theme_root/$stylesheet/plaintext");
			$plaintext_stylesheet_dir   = (empty($plaintext_stylesheet_files)) ? '/' : "$theme_root/$stylesheet/plaintext";

			$template_files = $this->get_template_files($template_directory, $template_files);
			$template_dir   = (empty($template_files)) ? '/' : $template_directory;

			$plaintext_template_files = $this->get_template_files("$template_directory/plaintext");
			$plaintext_template_dir   = (empty($plaintext_template_files)) ? '/' : "$template_directory/plaintext";

			$plaintext_template_files = $this->get_template_files(dirname($template_directory) . '/plaintext', array_merge($plaintext_stylesheet_files, $plaintext_template_files));

			// Check for theme name collision.
			if ( isset($mp_themes[$name]) ) {
				$trump_cards = array(
					'MailPress'	=> 'MailPress Theme',
					'nogent94'	=> 'Nogent94',
					self::default_theme_folder	=> self::default_theme_name,
					'nohtml'	=> 'nohtml',
				);
				if ( isset( $trump_cards[ $stylesheet ] ) && $name == $trump_cards[ $stylesheet ] ) {
					// If another theme has claimed to be one of our default themes, move
					// them aside.
					$suffix = $mp_themes[$name]['Stylesheet'];
					$new_name = "$name/$suffix";
					$mp_themes[$new_name] = $mp_themes[$name];
					$mp_themes[$new_name]['Name'] = $new_name;
				} else {
					$name = "$name/$stylesheet";
				}
			}

			$this->theme_roots[$stylesheet] = str_replace( MP_CONTENT_DIR, '', $theme_root );
			$mp_themes[$name] = array(
				'Name' 				=> $name,
				'Title' 				=> $title,
				'Description' 			=> $description,
				'Author' 				=> $author,
				'Author Name' 			=> $theme_data['AuthorName'],
				'Author URI' 			=> $theme_data['AuthorURI'],
				'Version' 				=> $version,
				'Template' 				=> $template,
				'Stylesheet' 			=> $stylesheet,
				'Template Files' 			=> $template_files,
				'Plaintext Template Files' 	=> $plaintext_template_files,
				'Stylesheet Files' 		=> $stylesheet_files,
				'Template Dir' 			=> str_replace(ABSPATH, '', $template_dir),
				'Plaintext Template Dir' 	=> str_replace(ABSPATH, '', $plaintext_template_dir),
				'Stylesheet Dir' 			=> str_replace(ABSPATH, '', $stylesheet_dir),
				'Plaintext Stylesheet Dir' 	=> str_replace(ABSPATH, '', $plaintext_stylesheet_dir),
				'Status' 				=> $theme_data['Status'],
				'Screenshot' 			=> $screenshot,
				'Tags' 				=> $theme_data['Tags'],
				'Theme Root' 			=> $theme_root,
				'Theme Root URI' 			=> str_replace(ABSPATH, site_url() . '/' , $theme_root),
			);
		}

		unset($theme_files);

		/* Resolve theme dependencies. */
		$theme_names = array_keys( $mp_themes );
		foreach ( (array) $theme_names as $theme_name ) 
		{
			$mp_themes[$theme_name]['Parent Theme'] = '';
			if ( $mp_themes[$theme_name]['Stylesheet'] != $mp_themes[$theme_name]['Template'] ) 
			{
				foreach ( (array) $theme_names as $parent_theme_name ) 
				{
					if ( ($mp_themes[$parent_theme_name]['Stylesheet'] == $mp_themes[$parent_theme_name]['Template']) && ($mp_themes[$parent_theme_name]['Template'] == $mp_themes[$theme_name]['Template']) ) 
					{
						$mp_themes[$theme_name]['Parent Theme'] = $mp_themes[$parent_theme_name]['Name'];
						break;
					}
				}
			}
		}
		return $mp_themes;
	}

	function get_theme($theme) 
	{
		$themes = $this->themes;
	
		if ( array_key_exists($theme, $themes) )	
			return $themes[$theme];

		return null;
	}

	function get_current_theme() 
	{
		if ( $theme = get_option(self::option_current_theme) )
			return $theme;

		$themes = $this->themes;
		$current_theme = self::default_theme_name;

		if ( $themes ) {
			$theme_names = array_keys($themes);
			$current_template = get_option(self::option_template);
			$current_stylesheet = get_option(self::option_stylesheet);

			foreach ( (array) $theme_names as $theme_name ) {
				if ( $themes[$theme_name]['Stylesheet'] == $current_stylesheet &&
						$themes[$theme_name]['Template'] == $current_template ) {
					$current_theme = $themes[$theme_name]['Name'];
					break;
				}
			}
		}

		update_option(self::option_current_theme, $current_theme);

		return $current_theme;
	}

	function get_theme_directories()
	{
		$theme_directories[] = $this->get_theme_root();

		$theme_directories = apply_filters('MailPress_theme_directories', $theme_directories);
		foreach($theme_directories as $key => $theme_directory)
		{
			$theme_directory = ( !file_exists( $theme_directory ) ) ? MP_CONTENT_DIR . '/' . $theme_directory : $theme_directory;
			if ( !file_exists( $theme_directory ) || (strpos($theme_directory, ABSPATH) !== 0) ) unset($theme_directories[$key]);
		}

		return $theme_directories;
	}

	function search_theme_directories() 
	{
		global $mp_theme_directories, $mp_broken_themes;

		if ( empty( $mp_theme_directories ) ) return false;

		$theme_files = array();
		$mp_broken_themes = array();

		foreach ( (array) $mp_theme_directories as $theme_root ) {
			/* Files in the root of the current theme directory and one subdir down */
			$themes_dir = @ opendir($theme_root);

			if ( !$themes_dir )
				return false;

			while ( ($theme_dir = readdir($themes_dir)) !== false ) {
				if ( is_dir($theme_root . '/' . $theme_dir) && is_readable($theme_root . '/' . $theme_dir) ) {
					if ( $theme_dir{0} == '.' || $theme_dir == 'CVS' )
						continue;

					$stylish_dir = @opendir($theme_root . '/' . $theme_dir);
					$found_stylesheet = false;

					while ( ($theme_file = readdir($stylish_dir)) !== false ) {
						if ( $theme_file == 'style.css' ) {
							$theme_files[$theme_dir] = array( 'theme_file' => $theme_dir . '/' . $theme_file, 'theme_root' => $theme_root );
							$found_stylesheet = true;
							break;
						}
					}
					@closedir($stylish_dir);

					if ( !$found_stylesheet ) { // look for themes in that dir
						$subdir = "$theme_root/$theme_dir";
						$subdir_name = $theme_dir;
						$theme_subdirs = @ opendir( $subdir );

						$found_subdir_themes = false;
						while ( ($theme_subdir = readdir($theme_subdirs)) !== false ) {
							if ( is_dir( $subdir . '/' . $theme_dir) && is_readable($subdir . '/' . $theme_dir) ) {
								if ( $theme_dir{0} == '.' || $theme_dir == 'CVS' )
									continue;

								$stylish_dir = @ opendir($subdir . '/' . $theme_subdir);
								$found_stylesheet = false;

								while ( ($theme_file = readdir($stylish_dir)) !== false ) {
									if ( $theme_file == 'style.css' ) {
										$theme_files["$theme_dir/$theme_subdir"] = array( 'theme_file' => $subdir_name . '/' . $theme_subdir . '/' . $theme_file, 'theme_root' => $theme_root );
										$found_stylesheet = true;
										$found_subdir_themes = true;
										break;
									}
								}
								@closedir($stylish_dir);
							}
						}
						@closedir($theme_subdirs);
						if ( !$found_subdir_themes )
							$mp_broken_themes[$theme_dir] = array('Name' => $theme_dir, 'Title' => $theme_dir, 'Description' => __('Stylesheet is missing.'), 'Folder' => basename($subdir));
					}
				}
			}
			@closedir( $themes_dir );
		}
		return $theme_files;
	}

	function get_theme_root($folder = false) 
	{
		if ( $folder && ( $theme_root = $this->get_raw_theme_root($folder) ) ) $theme_root = ABSPATH . MP_PATH_CONTENT . $theme_root;
		else $theme_root = ABSPATH . MP_PATH_CONTENT . 'themes';

		return apply_filters('MailPress_theme_root', $theme_root, $folder);
	}

	function get_raw_theme_root($folder) 
	{
		global $mp_theme_directories;
 
		if ( count($mp_theme_directories) <= 1 ) return 'themes';

		return ( empty($this->theme_roots[$folder]) ) ? false : $theme_root = $this->theme_roots[$folder];
	}

	function switch_theme($template, $stylesheet) 
	{
		update_option(self::option_template, $template);
		update_option(self::option_stylesheet, $stylesheet);

		delete_option(self::option_current_theme);
		$theme = $this->get_current_theme();

		do_action('MailPress_switch_theme', $theme);
	}

	function validate_current_theme() 
	{
		if ( $this->get_template() != self::default_theme_folder && !is_file($this->get_template_directory() . '/index.php') ) {
			$this->switch_theme(self::default_theme_folder, self::default_theme_folder);
			return false;
		}

		if ( $this->get_stylesheet() != self::default_theme_folder && !is_file($this->get_template_directory() . '/style.css') ) {
			$this->switch_theme(self::default_theme_folder, self::default_theme_folder);
			return false;
		}

		if ( $this->is_child_theme() && !is_file($this->get_stylesheet_directory() . '/style.css' ) ) {
			$this->switch_theme(self::default_theme_folder, self::default_theme_folder);
			return false;
		}
		return true;
	}
}
