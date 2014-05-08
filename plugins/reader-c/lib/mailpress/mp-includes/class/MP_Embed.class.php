<?php
class MP_Embed 
{
	var $handlers = array();

	/**
	 * Constructor
	 */
	function __construct() 
	{
		// Attempts to embed all URLs in a post in a mail
		if ( !get_option('embed_autourls') ) return;

		add_filter( 'the_content', array($this, 'autoembed'), 8 );
	}

	function register_handler( $id, $regex, $callback, $priority = 10 ) 
	{
		$this->handlers[$priority][$id] = array(
			'regex'    => $regex,
			'callback' => $callback,
		);
	}

	function unregister_handler( $id, $priority = 10 ) 
	{
		if ( isset($this->handlers[$priority][$id]) ) unset($this->handlers[$priority][$id]);
	}

	function autoembed( $content ) 
	{
		return preg_replace_callback( '|^\s*(https?://[^\s"]+)\s*$|im', array($this, 'autoembed_callback'), $content );
	}

	function autoembed_callback( $match ) 
	{
		$return = $this->shortcode( array(), $match[1] );
		return "\n$return\n";
	}

	function shortcode( $attr, $url = '' ) 
	{
		if ( empty($url) ) return '';

		global $post;
		$post_ID = ( !empty($post->ID) ) ? $post->ID : null;
//		if (!$post_ID) return $url;
		if (!$post_ID) $post_ID = -1;

		$rawattr = $attr;
		$attr = wp_parse_args( $attr, wp_embed_defaults() );

		// kses converts & into &amp; and we need to undo this
		// See http://core.trac.wordpress.org/ticket/11311
		$url = str_replace( '&amp;', '&', $url );

		// Look for known internal handlers
		ksort( $this->handlers );
		foreach ( $this->handlers as $priority => $handlers ) 
		{
			foreach ( $handlers as $id => $handler ) 
			{
				if ( preg_match( $handler['regex'], $url, $matches ) && is_callable( $handler['callback'] ) ) 
				{
					if ( false !== $return = call_user_func( $handler['callback'], $matches, $attr, $url, $rawattr ) )
						return apply_filters( 'mp_embed_handler_html', $return, $url, $attr );
				}
			}
		}

		// Unknown URL format. Let oEmbed have a go.
		if ( MailPress_embed::usecache )
		{
			$cachekey = MailPress_embed::meta_key . md5( $url . serialize( $attr ) );
			$html = get_post_meta( $post_ID, $cachekey, true );
			if ( MailPress_embed::unknown === $html ) 	return $url;		// Failures are cached
			if ( !empty($html) )					return apply_filters( MailPress_embed::html_filter, $html, $url, $attr, $post_ID );
		}

		// Use oEmbed to get the HTML
		$attr['discover'] = ( apply_filters('mp_embed_oembed_discover', false) && author_can( $post_ID, 'unfiltered_html' ) );
		$html = MailPress_Embed::_oembed_get( $url, $attr );

		if ( MailPress_embed::usecache )
			update_post_meta( $post_ID, $cachekey, ( $html ) ? $html : MailPress_embed::unknown );

		if ( $html )
			return apply_filters( MailPress_embed::html_filter, $html, $url, $attr, $post_ID );

		// Still unknown
		return $url;
	}
}