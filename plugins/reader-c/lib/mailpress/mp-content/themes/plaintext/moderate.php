<?php
/*
Template Name: moderate
*/
$comment = $this->args->advanced->comment;
$url     = $this->args->advanced->url;

$_the_title = (isset($url['approve']))
						? sprintf( __('A new comment on the post "%s" is waiting for your approval'), '{{the_title}}' )
						: sprintf( __('New comment on your post "%s"'), '{{the_title}}' );

$_the_content  = sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment->author_domain);
$_the_content .= "\n"; 
$_the_content .= sprintf( __('E-mail : %s'), $comment->comment_author_email);
$_the_content .= "\n"; 
$_the_content .= sprintf( __('URL    : %s'), $comment->comment_author_url);
$_the_content .= "\n"; 
$_the_content .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP );
$_the_content .= "\n\n"; 
$_the_content .= __('Comment : ');
$_the_content .= "\n"; 
$_the_content .= $comment->comment_content;
$_the_content .= "\n\n"; 

$moderator = (isset($mail->p->title)) ? true : false;

$_the_actions  = '';
$_the_actions .= (isset($url['approve']))
						? __('Approve')  . " [{$url['approve']}]\n"
						: __('View all') . " [{$url['comments']}#comments\n";
$_the_actions .= ( EMPTY_TRASH_DAYS ) 
						? __('Trash')    . " [{$url['trash']}]\n"
						: __('Delete')   . " [{$url['delete']}]\n";
$_the_actions .=				  __('Spam')     . " [{$url['spam']}]\n";

$this->get_template_part('_mail');