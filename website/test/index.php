<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>
	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />
	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	
	<title>Revise it</title>
</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>
	
	<div id="content">
	
		<?php
			echo $_SERVER["REQUEST_URI"];
		?>

		<script type="text/javascript">

		</script>
	
		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
