<?php
/*
Template Name: comments
*/
$comment = $this->args->advanced->comment;
$post    = $this->args->advanced->post;

$this->build->_the_title = sprintf( 'Comment # %s in "{{the_title}}"', $comment->comment_ID );

$this->build->_the_actions 	= __('Reply') . " [{$post->guid}&replytocom={$comment->comment_ID}#respond]";

$this->get_template_part('_mail');