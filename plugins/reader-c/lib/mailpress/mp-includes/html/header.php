<?php if (!isset($nodoctype)) : ?>
<!DOCTYPE html>
<?php endif; ?>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
		<title><?php if (isset($mp_title)) echo $mp_title; ?></title>