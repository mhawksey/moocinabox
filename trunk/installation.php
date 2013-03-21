<?php
	function readerlite_mar_install() {
		global $wpdb;
	
		$table_name =  $wpdb->prefix . "readerlite_mark_as_read_data";
		//if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
			$sql = "CREATE TABLE " . $table_name . " (
			id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
			userid int(11) UNSIGNED NOT NULL ,
			postid int(11) UNSIGNED NOT NULL ,
			updated datetime NOT NULL,
			CONSTRAINT tb_uq UNIQUE (userid , postid)
			)";			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
		//}
	}
?>