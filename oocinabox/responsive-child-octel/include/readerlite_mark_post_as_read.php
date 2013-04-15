<?php
// Based on Gaya Kessler's [http://www.gayadesign.com/] Mark as Read [http://www.gayadesign.com/general/wordpress-plugin-mark-as-read/]

function readerlite_mark_post_as_read() {
	global $post;
	$postid = $post->ID;
	$userid = get_current_user_id();
	global $wpdb;
	$table_name =  $wpdb->prefix . "readerlite_mark_as_read_data";
	$wpdb->query("INSERT INTO " . $table_name . " (postid, userid, updated) VALUES (" . $postid . ", " . $userid . ", NOW()) ON DUPLICATE KEY UPDATE updated = NOW()");
}

function readerlite_get_if_read_post($postid){
	global $wpdb;
	$table_name =  $wpdb->prefix . "readerlite_mark_as_read_data";
	$userid = get_current_user_id();
	if ($userid <= 0){
		return "unread";	
	}
	$check = $wpdb->get_results("SELECT updated FROM " . $table_name . " WHERE userid = " . $userid . " AND postid = " . $postid);
	$return = "unread";
	if (!empty($check)){
		$return = "read";
	}
	echo $return;
}
?>