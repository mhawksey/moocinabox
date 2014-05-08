<?php
class MP_AdminPage extends MP_adminpage_
{
	const screen 		= 'mailpress_revision';
	const capability 	= 'MailPress_edit_mails';
	const help_url		= false;
	const file        	= __FILE__;

////  Redirect  ////

	public static function redirect() 
	{
		$action	= (isset($_GET['action'])) 	? $_GET['action'] : false;

		$revision_id= (isset($_GET['revision'])) 	? absint($_GET['revision']) : false;
		$id		= absint($_GET['id']);

		$left		= (isset($_GET['left'])) 	? absint($_GET['left']) : false;
		$right	= (isset($_GET['right'])) 	? absint($_GET['right']) : false;

		$redirect_to = MailPress_edit;

		switch ( $action )
		{
			case 'delete' :
			break;
			case 'edit' :
			break;
			case 'restore' :
				if (!$revision = MP_Mail::get($revision_id)) break;
				if (!$mail     = MP_Mail::get($id))          break;

				$_POST = get_object_vars($mail);
				foreach(array('toname', 'subject', 'html', 'plaintext') as $k) if ($_POST[$k]) $_POST[$k] = addcslashes($_POST[$k], "'");
				MP_Mail_draft::update($revision_id, '');

				$_POST = get_object_vars($revision);
				unset($_POST['created']);
				foreach(array('toname', 'subject', 'html', 'plaintext') as $k) if ($_POST[$k]) $_POST[$k] = addcslashes($_POST[$k], "'");
				MP_Mail_draft::update($id);

				$redirect_to .= "&id=$id&revision=$revision_id&message=5&time=" . urlencode($revision->created);
			break;
			case 'diff' :
				$redirect_to .= "&id=$id";

				if ( $left == $right ) 
				{
					$redirect = $redirect_to;
					include(ABSPATH . 'wp-admin/js/revisions-js.php' );
					break;
				}

				if ( !$left_revision  = MP_Mail::get( $left ) )
					break;
				if ( !$right_revision = MP_Mail::get( $right ) )
					break;

				if ( strtotime($right_revision->created) < strtotime($left_revision->created) ) 
				{
					$redirect_to = MailPress_revision;
					$redirect_to .= "&action=diff&id=$id&left=$right_revision->id&right=$left_revision->id";
					break;
				}

				if ($left_revision->id  == $id) $left_ok = true;
				if ($right_revision->id == $id) $right_ok = true;
				$rev_ids = MP_Mail_meta::get($id, '_MailPress_mail_revisions');
				foreach ($rev_ids as $v) if ($left_revision->id  == $v) $left_ok = true;
				foreach ($rev_ids as $v) if ($right_revision->id == $v) $right_ok = true;
				if (!($left_ok && $right_ok)) break;
				$redirect_to = false;
			break;
			case 'view' :
			default :
 				if ( !$revision = MP_Mail::get( $revision_id ) )
					break;
				if ( !$mail = MP_Mail::get( $id ) )
					break;

				$redirect_to = false;
			break;
		}
		if ($redirect_to) self::mp_redirect( $redirect_to );
	}

////  Title  ////

	public static function title() { global $title; $title = __('Mail Revisions', MP_TXTDOM); }

////  Styles  ////

	public static function print_styles($styles = array()) 
	{
		wp_register_style ( self::screen, '/' . MP_PATH . 'mp-admin/css/write.css', array('thickbox') );

		$styles[] = self::screen;
		parent::print_styles($styles);
	}
}