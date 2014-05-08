<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width" />
		<title><?php bloginfo( 'name' ) ?> > <?php $this->the_subject('mail subject'); ?> > {{toemail}}</title>
<?php $this->get_stylesheet(); ?>
	</head>