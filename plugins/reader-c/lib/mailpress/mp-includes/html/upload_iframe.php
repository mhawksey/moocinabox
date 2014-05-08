<?php
$nodoctype = true; 
include('header.php');
do_action('admin_print_scripts');
?>
	</head>
	<body style='margin:0;padding:0;overflow:hidden;background:transparent;'>
		<form id='upload_form_<?php echo $id; ?>' action='<?php echo MP_Action_url; ?>' method='POST' enctype='multipart/form-data' style='margin:0;padding:0;overflow:hidden;cursor:default;'>
			<input type='hidden' name='action' 		value='html_mail_attachement' />
			<input type='hidden' name='draft_id' 	value='<?php echo $draft_id; ?>' />
			<input type='hidden' name='id' 		value='<?php echo $id; ?>' />
			<input type='hidden' name='max_file_size' value='<?php echo $bytes; ?>' />
			<label class='mp_fileupload_file' id='mp_fileupload_file' style='cursor:default;height:24px;width:132px;display:block;overflow:hidden;background:transparent url(images/upload.png) repeat;'>
				<input type='file' id='mp_fileupload_file_<?php echo $id; ?>' name='async-upload' style='cursor:default;height:24px;width:132px;margin:0;opacity:0;-moz-opacity:0;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);position:relative;' />
			</label>
			<input type='submit' id='upload_iframe_submit_<?php echo $id; ?>' style='display:none;cursor:default;' />
		</form>
<?php do_action('admin_print_footer_scripts'); ?>
	</body>
</html>