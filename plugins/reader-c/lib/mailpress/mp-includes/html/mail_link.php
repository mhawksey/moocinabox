<?php

$results = MP_Mail_links::process();

if (isset($_GET['view']))
{
	$mp_title = $results ['title'];
	include('header.php');
?>
	</head>
	<body>
		<div>
			<div>
				<b><?php echo $results ['title']; ?></b>
			</div>
			<?php echo $results ['content']; ?>
		</div>
	</body>
</html>
<?php
	return true;
}

get_header();
?>
	<div id='content' class='widecolumn'>
		<div>
			<h2><?php echo $results ['title']; ?></h2>
			<div>
				<?php echo $results ['content']; ?>
			</div>
		</div>
	</div>
<?php
get_footer();
?>