<?php
if (class_exists('MailPress') && !class_exists('MailPress_embed'))
{
/*
Plugin Name: MailPress_embed 
Plugin URI: http://blog.mailpress.org/tutorials/add-ons/embed/
Description: Mail oEmbed (equivalent of WordPress video embedding but for mails)
Version: 5.4
*/

class MailPress_embed
{
	public static $type = null;
	public static $mp_oembed = null;

	const meta_key = '_mp_oembed_';
	const usecache = true;
	const html_filter = 'mp_embed_oembed_html';
	const unknown = '{{unknown}}';

	function __construct()
	{
		add_action( 'save_post', 						array(__CLASS__, 'save_post') );

		add_action('MailPress_build_mail_content_start',	array(__CLASS__, 'build_mail_content_start'));
		add_action('MailPress_build_mail_content_end',		array(__CLASS__, 'build_mail_content_end'));

		add_filter('mp_oembed_providers',				array(__CLASS__, 'instagram'));
	}

	public static function instagram($providers)
	{
		$providers['#http://(www\.)?instagr\.am/.*#i'] = array( 'http://api.instagram.com/oembed', true );
		return $providers;
	}

	public static function build_mail_content_start($type)
	{
		global $wp_embed, $mp_embed;
		remove_filter('the_content', array($wp_embed, 'autoembed'), 8 );

		self::$type = $type;

		if ('html' == $type)
		{
			$mp_embed = new MP_Embed();
			self::embed_register_handler( 'googlevideo', '#http://video\.google\.([A-Za-z.]{2,5})/videoplay\?docid=([\d-]+)(.*?)#i', array(__CLASS__, 'embed_handler_googlevideo') );
			do_action('MailPress_embed_register_handler');
		}
	}

	public static function build_mail_content_end($type)
	{
		global $wp_embed, $mp_embed;
		add_filter('the_content', array($wp_embed, 'autoembed'), 8 );

		if ('html' == $type)
		{
			remove_filter('the_content', array($mp_embed, 'autoembed'), 8 );
			$mp_embed = self::$type = null;
		}
	}

	public static function save_post($post_ID)
	{
		$post_metas = get_post_custom_keys($post_ID);
		if (empty($post_metas))	return;

		foreach($post_metas as $post_meta_key)
			if (self::meta_key == substr($post_meta_key, 0, strlen(self::meta_key)))
				delete_post_meta($post_ID, $post_meta_key);
	}

	public static function embed_register_handler( $id, $regex, $callback, $priority = 10 )
	{
		global $mp_embed;
		$mp_embed->register_handler( $id, $regex, $callback, $priority );
	}

	public static function embed_unregister_handler( $id, $priority = 10 )
	{
		global $mp_embed;
		$mp_embed->unregister_handler( $id, $priority );
	}




	public static function embed_handler_googlevideo( $matches, $attr, $url, $rawattr )
	{
		if (!isset($matches[2])) return $url;

		global $post;
		$post_ID = ( !empty($post->ID) ) ? $post->ID : null;
		if (!$post_ID) return $url;

		$docid = (string) $matches[2];
		if ('-' == $docid[0]) $docid = substr($docid, 1);
		$search_url = "https://ajax.googleapis.com/ajax/services/search/video?v=1.0&q={$docid}";

		if ( self::usecache )
		{
			$cachekey = self::meta_key . md5( $url );
			$html = get_post_meta( $post_ID, $cachekey, true );
			if ( self::unknown === $html ) 	return $url;		// Failures are cached
			if ( !empty($html) )			return apply_filters( self::html_filter, $html, $url, $attr, $post_ID );
		}

		// Use video search
		$json = get_object_vars(json_decode(file_get_contents($search_url)));
		if (isset($json['responseData']->results[0]) && 200 == $json['responseStatus'])
		{
			$r = $json['responseData']->results[0];

			$moves = array(	'thumbnail_url' => 'tbUrl', 'thumbnail_width' => 'tbWidth', 'thumbnail_height' => 'tbHeight', 'title' => 'title' );
			$data = new stdClass();
			foreach($moves as $k => $v) $data->{$k} = $r->{$v};
			$data->url = $url;

			$html = self::_embed_get($data);
		}

		if ( MailPress_embed::usecache )
			update_post_meta( $post_ID, $cachekey, ( $html ) ? $html : self::unknown );

		if ( $html )
			return apply_filters( self::html_filter, $html, $url, $attr, $post_ID );

		// Still unknown
		return $url;
	}




	public static function _embed_get($data)
	{
		$html  = "<a target='_blank' href=\"" . esc_url($data->url) . "\"";
		$html .= " title=\"" . esc_html($data->title) . "\"";
		$html .= ">";

		$html .= "<img";
		$html .= " width='{$data->thumbnail_width}px'";
		$html .= " height='{$data->thumbnail_height}px'";
		$html .= " src='{$data->thumbnail_url}'";
		$html .= " title=\"" . esc_html($data->title) . "\" alt=\"" . esc_html($data->title) . "\"";
		$html .= " />";

		$html .= "</a>";

		return $html;
	}




	public static function _oembed_get($url, $args = '')
	{
		require_once ( ABSPATH . WPINC . '/class-oembed.php' );
		$oembed = self::_oembed_get_object();
		return $oembed->get_html( $url, $args );
	}

	public static function _oembed_add_provider($format, $provider, $regex = false)
	{
		require_once ( ABSPATH . WPINC . '/class-oembed.php' );
		$oembed = self::_oembed_get_object();
		$oembed->providers[$format] = array( $provider, $regex );
	}

	public static function &_oembed_get_object() 
	{
		if ( is_null(self::$mp_oembed) ) self::$mp_oembed = new MP_oEmbed();
		self::$mp_oembed->providers = apply_filters('mp_oembed_providers', self::$mp_oembed->providers);
		return self::$mp_oembed;
	}
}
new MailPress_embed();
}