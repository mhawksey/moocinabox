<?php
abstract class MP_mail_ extends MP_db_connect_
{
	const default_theme = 'twentyten';
	public static $_classes = array();
	public static $_classes_1st = true;

	// feeding mail contents

	function the_title($before = '', $after = '', $echo = true) 
	{
		$title = $this->get_the_title();
		if ( strlen($title) == 0 ) return;
		$title = $before . $title . $after;
		if ( $echo ) echo   $title;
		else         return $title;
	}

	function get_the_title()
	{
		if (isset($this->args->newsletter) && $this->args->newsletter)
			return ($this->build->plaintext) ? $this->html_entity_decode(get_the_title()) : get_the_title();

		return isset($this->row->subject) ? $this->row->subject : '';
	}

	function the_content($more_link_text = null, $stripteaser = 0)
	{
		$content = $this->get_the_content($more_link_text, $stripteaser);
		echo ($this->build->plaintext) ? $content : ( ($this->build->filter) ? apply_filters($this->build->filter, $content) : $content );
	}

	function get_the_content($more_link_text = null, $stripteaser = 0)
	{
		$content = '';

		if (isset($this->args->newsletter) && $this->args->newsletter)
		{
			if (null !== $more_link_text) 
			{
				global $more; 
				$_more = $more; 
				$more = false;
			}
			$content = get_the_content($more_link_text, $stripteaser);
			if (null !== $more_link_text)
			{
				$more = $_more;
				$content = preg_replace('/class=[\'"]more-link[\'"]/i', 'class="more-link" ' . $this->classes('more-link', false), $content);
			}
			return ($this->build->plaintext) ? $this->html2txt(apply_filters('the_content', $content)) : $content;
		}

		if (isset($this->args->content) && !empty($this->args->content))
			$content = ($this->build->plaintext) ? $this->html2txt(apply_filters('the_content', $this->args->content)) : $this->args->content;

		if ($this->build->plaintext && isset($this->args->plaintext) && !empty($this->args->plaintext))
			$content = $this->args->plaintext;

		if (!$this->build->plaintext && isset($this->args->html)     && !empty($this->args->html))
			$content = $this->args->html;

		return $content; 
	}

	function the_image($args = array())
	{
		if ($this->build->plaintext) return;
		echo $this->get_the_image($args);
	}

	function get_the_image($args = array())
	{
		if ($this->build->plaintext) return '';

		$defaults = array (	'pick'	=> 1, // 0 is random, 1 is first ... x if not found search for x-1
						'attrs' 	=> array(),

						'unit'	=> 'px',
						'max_width'	=> 100,
						'force_max_width'	=> 1,
						'max_height'=> 100,
						'force_max_height'=> 1,
						'priority'	=> 1, // 0 width unchanged, 1 height unchanged
					);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );
		if (!is_array($attrs)) $attrs = array();

	// analyse post content
		ob_start();
			the_content();
			$html = ob_get_contents();
		ob_end_clean();

		$output = preg_match_all('/<img[^>]*>/Ui', $html, $imgtags, PREG_SET_ORDER); // all img tag
		foreach ($imgtags as $imgtag)
		{
			$output = preg_match_all('/src=[\'"]([^\'"]+)[\'"]/Ui', $imgtag[0], $src, PREG_SET_ORDER); // for src attribute
			$matches[] = array(0 => $imgtag[0], 1 => $src[0][1]);
		}
		if (empty($matches)) return '<!-- MailPress_mail_api ** no img detected ** -->';

	// pick image
		if ($pick) 	do { $pick--; if (isset($matches[$pick])) $img = $matches[$pick]; } while ( !isset($img) );
		else		$img = $matches[array_rand($matches)];

	// compute width & height
		$hw = @getimagesize($img[1]);
		if (!$hw) return '<!-- MailPress_mail_api ** getimagesize failed ** -->';

		$width = ($max_width  && (force_max_width  || $max_width  < $hw[0])) ? $max_width  : $hw[0];
		$height= ($max_height && (force_max_height || $max_height < $hw[1])) ? $max_height : $hw[1];

		if ( ($hw[0]/$hw[1]) != ($width/$height) )
		{
			if ($priority)	$width  = round ( $height * $hw [0] / $hw [1] );
			else			$height = round ( $width  * $hw [1] / $hw [0] );
		}

	// formatting args style
		$style = array();
		if (isset($attrs['style']))
		{
			$style = (is_array($attrs['style'])) ? $attrs['style'] : $this->retrieve_styles($attrs['style']);
			unset($attrs['style']);
		}

	// merging existing and args attributes
		$attrs = array_merge($this->retrieve_attributes($img[0]), $attrs);
		$attrs['style'] = (isset($attrs['style'])) ? array_merge($this->retrieve_styles($attrs['style']), $style) : $style;

	// width & height
		unset($attrs['width'], $attrs['height']);
		$attrs['style']['width']  = $width  . $unit;
		$attrs['style']['height'] = $height . $unit;

	// convert $attrs['style'] from array to string
		$wstyle = '';
		$quote = '"';
		foreach ($attrs['style'] as $k => $v)
		{
			if (false !== strpos($v, '"')) $quote = "'";
			if ($v != '') $wstyle .= "$k:$v;";
		}
		$attrs['style'] = $wstyle;

	// build new img tag
		$wimg = '';
		foreach ($attrs as $k => $v) $wimg .= ('style' == $k) ? " $k=$quote$v$quote" : " $k=\"$v\"";

		return "<!-- MailPress_mail_api start -->\n<img$wimg />\n<!-- MailPress_mail_api end -->";
	}

	function retrieve_attributes($img)
	{
		if (empty($img)) return array();

		$w = str_ireplace('<img ', '', $img);
		$w = str_ireplace('/>', '', $w);
		$w = trim($w);
		do {$w = str_ireplace('  ', ' ', $w, $count);} while ($count);
		do {$w = str_ireplace(' =', '=', $w, $count);} while ($count);
		do {$w = str_ireplace('= ', '=', $w, $count);} while ($count);

		if ('' == $w) return array();

		do
		{
			$att 		= strpos($w, '=');
			$key		= substr($w, 0, $att);
			$quote 	= substr($w, $att+1, 1);
			if ("'" != $quote) if ('"' != $quote) $quote=false;
			$start 	= ($quote) ? 1 : 0;
			$end 		= ($quote) ? strpos($w, $quote, $att+1+$start) : strpos($w, ' ') ;
			$val 		= substr($w, $att+1+$start, $end-($att+1+$start));

			$x[trim($key)]=trim($val);

			$w = trim(substr($w, $end+1));
		} while ('' != $w);

		return $x;
	}

	function retrieve_styles($style)
	{
		if (empty($style)) return array();

		$w = explode(';', $style);
		foreach ($w as $v)
		{
			if ($v)
			{
				$zs = explode(':', $v);
				$x[trim($zs[0])] = trim($zs[1]);
			}
		}

		return $x;
	}

	// styling mails utilities

	function classes($classes, $echo = true, $attr = false)
	{
		if (self::$_classes_1st)
		{
			self::$_classes_1st = false;

			$files = $this->get_template_paths( 'style.php', true ) ;

			foreach ($files as $file)
			{
				if (!is_file($file)) continue;
				require_once($file);
				if (isset($_classes) && !empty($_classes)) self::$_classes[] = $_classes;
				unset($_classes);
			}
		}
		if (empty(self::$_classes)) return '';

		$count = 1;
		while ($count) $classes = str_replace('  ', ' ', $classes, $count);
		$a_classes = explode(' ', trim($classes));

		$style = '';

		foreach($a_classes as $class) foreach(self::$_classes as $_classes) if (isset($_classes[$class])) $style .= $this->clean_style($_classes[$class]);

		if ('' != $style) 
			if ($echo) 		echo   "style=\"" . $style . "\""; 
			elseif (!$attr)	return "style=\"" . $style . "\"";
			else			return $style;
	}

	function clean_style($style)
	{
		$style = trim($style);
		$style = str_replace("\t",'',$style);
		$style = str_replace("\n",'',$style);
		$style = str_replace("\r",'',$style);
		if (strlen($style)) if ($style[strlen($style) -1] != ';') $style .=';';
		return $style;
	}

	// convert html to txt

	function html_entity_decode($html)
	{
		if (!preg_match('/&[^&;]+;/i', $html)) return $html;
		$h = new MP_Html2txt( 'entities' );
		return $h->get_text( $html, 0 );
	}

	function html2txt($html)
	{
		if (empty($html)) return $html;
		$h = new MP_Html2txt();
		return $h->get_text( $html, 0 );
	}

	// special mail attributes (subject)

	function the_subject($default = '', $echo = true)
	{
		$subject = $this->get_the_subject($default);
		if ( strlen($subject) == 0 ) return;
		if ( $echo ) echo $subject;
		else         return $subject;
	}

	function get_the_subject($default)
	{
		if (isset($this->row->subject)) return $this->row->subject;
		return $default;
	}

	// special files (header, footer, stylesheet, sidebars)

	function get_header() 
	{
		$this->get_template_part('header');
	}

	function get_footer() 
	{
		$this->get_template_part('footer');
	}

	function get_sidebar( $name = null )
	{
		$this->get_template_part('sidebar', $name);
	}

	function get_stylesheet() 
	{
		foreach ($this->get_template_paths('style.css', true) as $file)
		{
			if ( !is_file( $file ) ) continue;
			echo "<style type='text/css' media='all'>\n";
			include( $file );
			echo "</style>\n";
		}
	}

	// file loaders

	function get_template_paths( $file, $desc = false ) 
	{
		$x = array($this->build->stylesheetpath . $file, $this->build->templatepath . $file);
		if ($desc) krsort($x);
		if (isset($this->build->plaintextpath)) $x[] = $this->build->plaintextpath . $file;
		return array_unique($x);
	}

	function get_template_part( $slug, $name = null ) 
	{
		$template = false;
		if ( isset($name) ) $template = "{$slug}-{$name}.php";
		else $template = "{$slug}.php";
		return $this->locate_template($template, true, false);
	}

	function locate_template($template_name = 'default.php', $load = false, $require_once = true ) 
	{
		$located = '';

		if ( !$template_name ) continue;

		foreach ($this->get_template_paths($template_name) as $file)
		{
			if ( is_file($file) )
			{
				$located = $file;
				break;
			}
		}

		if ('' == $located)
		{
			if (isset($this->trace))
			{
				$this->trace->log(sprintf('MAILPRESS [NOTICE] - Missing template : >> %1$s << Folder : %2$s ', $template_name, $this->build->stylesheetpath), $this->trace->levels[512]);
				if ($this->build->stylesheetpath != $this->build->templatepath)
					$this->trace->log(sprintf('MAILPRESS [NOTICE] - Missing template : >> %1$s << Folder : %2$s ', $template_name, $this->build->templatepath), $this->trace->levels[512]);
				if (isset($this->build->plaintextpath) && ($this->build->stylesheetpath != $this->build->plaintextpath) && ($this->build->plaintextpath != $this->build->templatepath))
					$this->trace->log(sprintf('MAILPRESS [NOTICE] - Missing template : >> %1$s << Folder : %2$s ', $template_name, $this->build->plaintextpath), $this->trace->levels[512]);
			}
		}
		elseif ( $load )
			$this->load_template( $located, $require_once );

		return $located;
	}

	function load_template( $_template_file, $require_once = false ) 
	{
		global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array( $wp_query->query_vars ) ) extract( $wp_query->query_vars, EXTR_SKIP );

		if ( $require_once ) 	require_once( $_template_file );
		else 				require( $_template_file );
	}
}