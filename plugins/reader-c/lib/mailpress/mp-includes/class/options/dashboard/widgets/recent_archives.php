<?php
global $mp_mails;
$mp_mails = new MP_Query();
$mp_mails->query();
if ($mp_mails->mail_count)
{
class MP_Dashboard_recent_archives extends MP_dashboard_widget_
{
	var $id = 'mp_recent_archives';

	function __construct($name)
	{
		wp_register_script('mp-thickbox', 		'/' . MP_PATH . 'mp-includes/js/mp_thickbox.js', array('thickbox'), false, 1);
		wp_enqueue_script ('mp-thickbox');

		parent::__construct($name);
	}

	function widget()
	{
		global $mp_mails;
		$i = 0;
?>
<ul>
<?php 			
		while ($mp_mails->have_mails()) : $mp_mails->the_mail();
			$args = array();
			$args['id'] 	= $mp_mails->get_the_ID();
			$args['action'] 	= 'iview';
			$args['preview_iframe'] = 1; $args['TB_iframe']= 'true';
			$view_url		= esc_url(MP_::url(MP_Action_url, $args));
?>
	<li id='mail-<?php $mp_mails->the_ID(); ?>'>
		<h4 style='font-weight:normal;'>
			<a class='thickbox thickbox-preview' title="<?php _e('View', MP_TXTDOM ); ?>" href="<?php echo $view_url; ?>" >
				<?php $mp_mails->the_subject(); ?>
			</a>
			<abbr title='<?php $mp_mails->the_date('Y/m/d g:i:s A'); ?>' style='color:#999;font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;font-size:11px;margin-left:3px;'>
				<?php $mp_mails->the_date(); ?>
			</abbr>
		</h4>
		<p style='margin:0;padding:0;'>
		<?php $mp_mails->the_Theme(); ?> (<?php $mp_mails->the_Template(); ?>)
		</p>
	</li>
<?php
			$i++;
			if ($i == 10) break;	
		endwhile;
?>
</ul>
<p class="textright">
	<a class="button" href="<?php echo MailPress_mails . '&amp;status=archived'; ?>">
		<?php _e('View all'); ?>
	</a>
</p>
<?php
	}
}
new MP_Dashboard_recent_archives(__( 'MailPress - Recent Archives', MP_TXTDOM ));
}