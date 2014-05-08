<?php
abstract class MP_import_importer_
{
	const bt 			= 133;
	var 	$trace_header 	= false;

	function __construct($desc, $header, $callback = false) 
	{
		$this->desc 	= $desc;
		$this->header 	= $header;
		if (!$callback) $this->callback = array($this, 'dispatch');

		add_filter('MailPress_import_importers_register', array($this, 'register'), 8, 1);
	}

	function register($importers) 
	{
		if ( is_wp_error( $this->callback ) ) return $importers;
		$importers[$this->id] = array ( $this->id, $this->desc, $this->callback);
		return $importers;
	}

	function header() 
	{
		echo "<div class='wrap'><div id='icon-mailpress-tools' class='icon32'><br /></div><h2>" . $this->header . '</h2>';
	}

	function footer() 
	{
		echo '</div>';
	}

// step 0

	function greet() 
	{
?>
<div>
	<p>
<?php		_e('Howdy! Upload your file and we&#8217;ll import the emails and much more ... into this blog.', MP_TXTDOM); ?>
		<br />
<?php		_e('Choose a file to upload, then click Upload file and import.', MP_TXTDOM); ?>
	</p>
<?php wp_import_upload_form( MailPress_import . '&amp;mp_import=' . $this->id . '&amp;step=1'); ?>
</div>
<?php
	}

// step 1

	function handle_upload() 
	{
		$file = wp_import_handle_upload();
		if ( isset($file['error']) )
		{
			$this->trace->end(true);
			$this->error('<p><strong>' . $file['error'] . '</strong></p>');
			return false;
		}
		
		$this->file    = $file['file'];
		$this->file_id = (int) $file['id'];
		return true;
	}

// for files

	function fopen($filename, $mode='r') 
	{
		if ( $this->has_gzip() ) return gzopen($filename, $mode);
		return fopen($filename, $mode);
	}

	function feof($fp) 
	{
		if ( $this->has_gzip() ) return gzeof($fp);
		return feof($fp);
	}

	function fgets($fp, $len=8192) 
	{
		if ( $this->has_gzip() ) return gzgets($fp, $len);
		return fgets($fp, $len);
	}

	function fclose($fp) 
	{
		if ( $this->has_gzip() ) return gzclose($fp);
		return fclose($fp);
	}

	function has_gzip() 
	{
		return is_callable('gzopen');
	}

// for db tables

	public static function tableExists($table) 
	{
		global $wpdb;
		return strcasecmp($wpdb->get_var("show tables like '$table'"), $table) == 0;
	}

// for logs

	function start_trace($step)
	{
		$this->trace = new MP_Log('mp_import_' . $this->id, array('option_name' => 'import'));
		$this->header_report($step);
	}

	function end_trace($rc)
	{
   		$this->footer_report();
		$this->trace->end($rc);
	}

	function header_report($step)
	{
		if ((isset($this->trace_header)) && $this->trace_header) return;
		$this->trace_header = true;
		$this->message_report(str_repeat( '-', self::bt));
		$this->message_report(str_repeat( ' ', 5) . "Batch Report   importer : " . $this->id . "     step : $step" );
		$this->message_report(str_repeat( '-', self::bt));
	}

	function message_report($bm)
	{
		if (!$this->trace_header) $this->header_report('?');

		$bl = strlen($bm);
		$bl = self::bt - $bl;
		$bl = ($bl < 0) ? 0 : $bl;
		$bm = '!' . $bm . str_repeat( ' ', $bl ) . '!';
		if ($this->trace) 
			$this->trace->log($bm);
		else
			echo '<pre>' . $bm . "</pre>\n";
	}

	function footer_report()
	{
		if ((isset($this->trace_footer)) && $this->trace_footer) return;
		$this->trace_footer = true;
		$this->message_report(str_repeat( '-', self::bt));
	}

	function link_trace()
	{
		if ( isset($this->trace->file) && is_file($this->trace->file) )
		{ 
			$file = $this->trace->file;
			$y = str_replace('\\', '/', substr( $this->trace->file, strpos( $this->trace->file, str_replace( ABSPATH, '', WP_CONTENT_DIR ) ) ) );
			return "<p><a href='../$y' target='_blank'>" . __('See the log', MP_TXTDOM) . '</a></p>';
		}
		return '';
	}

// for success & errors

	function success($text = '', $echo = true)
	{
		$x  = '<div><h3>' . __('Process successful', MP_TXTDOM) . '</h3>';
		$x .= $text;
		$x .= $this->link_trace();
		$x .= '</div>';

		if ($echo) echo $x;
		return $x;
	}

	function error($text = '', $echo = true)
	{
		$x  = '<div><h3>'.__('Sorry, there has been an error.', MP_TXTDOM).'</h3>';
		$x .= $text;
		$x .= $this->link_trace();
		$x .= '</div>';

		if ($echo) echo $x;
		return $x;
	}

////  IMPORT API  ////

	function sync_mp_user($email, $name, $status = 'active')
	{
		$xl = strlen($email);
		$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
		$x = $email . str_repeat( ' ', $xl);

		if ( !is_email($email))
		{
			$this->message_report("** ERROR ** ! $x not an email ($name)");
		 	return false;
		}
		
		if ( 'deleted' != MP_User::get_status_by_email($email) )
		{
			$this->message_report(" **WARNING* ! $x already exists ($name) (processed if extra work to do)");
		}
		else
		{
			MP_User::insert($email, $name, array('status' => $status, 'stopPropagation' => true));

			$this->message_report(" insert     ! $x inserted ($name)");
		}
		return MP_User::get_id_by_email($email);
	}

	function sync_mp_usermeta($mp_user_id, $meta_key, $meta_value)
	{
		if (!MP_User_meta::add(    $mp_user_id, $meta_key, $meta_value, true))
			MP_User_meta::update($mp_user_id, $meta_key, $meta_value);

		$this->message_report(" meta       ! user [$mp_user_id]=> update of meta data key=>\"$meta_key\" data=>\"$meta_value\"");
	}

	function sync_mailinglist($mailinglist) 
	{
		if (!class_exists('MailPress_mailinglist')) return false;

		if ($id = MP_Mailinglist::get_id('MailPress_import_' . $mailinglist))
		{
			$this->message_report(" mailinglist! mailing list found : [$id] => $mailinglist");
			return $id;
		}

		if ($id = MP_Mailinglist::insert(array('name'=>'MailPress_import_' . $mailinglist)))
		{
			$this->message_report(" mailinglist! mailing list inserted : [$id] => $mailinglist");
			return $id;
		}

		$this->message_report("** ERROR ** ! Unable to read or create a mailing list : $mailinglist");
		return false;
	}

	function sync_mp_user_mailinglist($mp_user_id, $mailinglist_ID, $email='', $mailinglist='', $trace=false) 
	{
		if (!class_exists('MailPress_mailinglist')) return false;

		$user_mailinglists = MP_Mailinglist::get_object_terms($mp_user_id);
		if (in_array($mailinglist_ID, $user_mailinglists))
		{
			$xl = strlen($email);
			$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
			$x = $email . str_repeat( ' ', $xl);
			$this->message_report(" mailinglist! $x [$mp_user_id] already in mailing list [$mailinglist_ID] => $mailinglist");
		}
		else
		{
			array_push($user_mailinglists, $mailinglist_ID);
			MP_Mailinglist::set_object_terms( $mp_user_id, $user_mailinglists );

			$xl = strlen($email);
			$xl = ((25 - $xl) < 0) ? 0 : 25 - $xl;
			$x = $email . str_repeat( ' ', $xl);
			$this->message_report(" mailinglist! $x [$mp_user_id] inserted in mailing list [$mailinglist_ID] => $mailinglist");
		}
	}

	function sync_mp_user_newsletter($mp_user_id) 
	{
		if (!class_exists('MailPress_newsletter')) return false;

		MP_Newsletter::set_object_terms( $mp_user_id, array_merge(MP_Newsletter::get_object_terms($mp_user_id), MP_Newsletter::get_defaults()) );		
	}

	function sync_mp_user_no_newsletter($mp_user_id) 
	{
		if (!class_exists('MailPress_newsletter')) return false;
		MP_Newsletter::set_object_terms( $mp_user_id );		
	}

////  ATTACHMENTS ////

	function insert_attachment($file)
	{
		$uploads = wp_upload_dir();

		if ( $uploads['error'] )
		{
			$this->message_report(' **WARNING* ! ' . $uploads['error']);
			return false;
		}

		$filename = wp_unique_filename( $uploads['path'], $file['name'] );

		$new_file = $uploads['path'] . "/$filename";

		if ( copy($file['tmp_name'], $new_file) ) 
		{
			@unlink($file['tmp_name']);
			
		// Set correct file permissions
			$stat = stat( dirname( $new_file ));
			$perms = $stat['mode'] & 0000666;
			@ chmod( $new_file, $perms );

		// Compute the URL
			$url = $uploads['url'] . "/$filename";

			$object = array( 	'post_title' => $filename,
						'post_content' => $url,
						'post_mime_type' => $file['type'],
						'guid' => $url,
						'context' => 'export',
						'post_status' => 'private'
			);
			wp_insert_attachment($object);
			return $url;
		}
		return false;
	}
}