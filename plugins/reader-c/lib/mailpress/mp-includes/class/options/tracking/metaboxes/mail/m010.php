<?php
class MP_Tracking_metabox_m010 extends MP_tracking_metabox_sysinfo_
{
	var $id	= 'm010';
	var $context= 'normal';
	var $file 	= __FILE__;

	var $item_id = 'mail_id';

	function extended_meta_box($tracks)
	{
		$this->_010($tracks);
	}
}
new MP_Tracking_metabox_m010(__('System info II', MP_TXTDOM));