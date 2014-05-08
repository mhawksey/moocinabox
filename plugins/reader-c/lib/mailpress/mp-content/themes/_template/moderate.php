<?php
/*
Template Name: moderate
*/
$comment = $this->args->advanced->comment;
$url     = $this->args->advanced->url;

extract(MP_theme_html_template_::who_is($comment->comment_author_IP));

$this->build->_the_title = (isset($url['approve']))
						? sprintf( __('A new comment on the post "%s" is waiting for your approval'), '{{the_title}}' )
						: sprintf( __('New comment on your post "%s"'), '{{the_title}}' );

$_the_content  = "<table style='border:none;width:100%;'><tr><td style='width:60%;vertical-align:top;padding:5px 5px 5px 0;'>";
$_the_content .= "<br />\n";
$_the_content .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain);
$_the_content .= "<br />\n"; 
$_the_content .= sprintf( __('E-mail : %s'), $comment->comment_author_email);
$_the_content .= "<br />\n"; 
$_the_content .= sprintf( __('URL    : %s'), $comment->comment_author_url);
$_the_content .= "<br />\n";
$_the_content .= __('Comment: ');
$_the_content .= "<br />\n"; 
$_the_content .= apply_filters('comment_text', $comment->comment_content);
$_the_content .= "<br />\n";
$_the_content .= "</td><td style='padding:5px 0 5px 5px;'>";
if ($src) $_the_content .= "<br />\r\n<img src='$src' alt='' /><br />\r\n";
if ($addr)$_the_content .= "<br />\r\nreverse geocoding : $addr<br />\r\n";
$_the_content .= "</td></tr></table>";
$_the_content .= "<br />\n";
$_the_content .= "<br />\n";
$this->build->_the_content = $_the_content;

$_the_actions  = '';
$_the_actions .= (isset($url['approve']))  
						  ? "<a " . $this->classes('button', false) . " href='{$url['approve']}'>"			. __('Approve')  . "</a>"
	 					  : "<a " . $this->classes('button', false) . " href='{$url['comments']}#comments'>"	. __('View all') . "</a>";
$_the_actions .= ( EMPTY_TRASH_DAYS ) ? "&#160;<a " . $this->classes('button', false) . " href='{$url['trash']}'>"		. __('Trash')    . "</a>"
						  : "&#160;<a " . $this->classes('button', false) . " href='{$url['delete']}'>"		. __('Delete')   . "</a>";
$_the_actions .=                        "&#160;<a " . $this->classes('button', false) . " href='{$url['spam']}'>"			. __('Spam')     . "</a>";
$this->build->_the_actions = $_the_actions;

$this->get_template_part('_mail');