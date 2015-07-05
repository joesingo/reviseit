<?php include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php"); ?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />

	<style type="text/css">
	
		textarea {
			width:80%;
			height:180px;
		}

	</style>

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>
	
	<div id="content">

		<h2>Feedback</h2>
		
		<p>
			Thanks for using Revise It. Any feedback would be much appreciated.
		</p>
		<p>Your message:</p>

		<form action="/feedback/save.php" method="POST">
			<textarea name="feedback"></textarea>
			<p><button>Send</button></p>

			<input type="hidden" name="uid" value="<?php echo $user_id; ?>" />
		</form>
	
		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
