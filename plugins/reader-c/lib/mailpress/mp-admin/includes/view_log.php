<?php
$f = $_GET['id'];
$view_url 	=  site_url() . '/' . MP_AdminPage::get_path() . '/' . $f;

$h2 = sprintf(__('Log : %1$s', MP_TXTDOM), $f);
?>
<div class='wrap'>
	<div id="icon-mailpress-tools" class="icon32"><br /></div>
	<div id='mp_message'></div>
	<h2><?php echo $h2; ?></h2>
<?php if (isset($message)) MP_AdminPage::message($message); ?>
	<div><p>&#160;</p></div>
	<iframe id='mp' name='mp' style='width:100%;' src='<?php echo $view_url; ?>' ></iframe>
</div>