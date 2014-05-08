<?php include('header.php'); ?>
		<style type="text/css">
			pre {
				white-space: pre-wrap; /* css-3 */
				white-space: -moz-pre-wrap; /* Mozilla, since 1999 */
				white-space: -pre-wrap; /* Opera 4-6 */
				white-space: -o-pre-wrap; /* Opera 7 */
				word-wrap: break-word; /* Internet Explorer 5.5+ */
			}
		</style>
	</head>
	<body>
		<pre><?php echo htmlspecialchars($plaintext, ENT_NOQUOTES); ?></pre>
	</body>
</html>