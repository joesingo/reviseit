<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>Oops... | Revise it</title>

	<link rel="stylesheet" type="text/css" href="styles/main_style.css" />

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/no_link_header.php"); ?>
	
	<div id="content">

		<h2>Error</h2>

		<?php

			echo "<p>There was an error (sorry)</p>";

			if (isset($_GET["e"])) {

				$errors = explode(",", $_GET["e"]);
				foreach ($errors as $i) {
					switch ($i) {
						case "badID":
							echo "<p class='error'>No set was found with this ID</p>";
							break;
						case "badGID":
							echo "<p class='error'>No game was found with this ID</p>";
							break;
						case "dbError":
							echo "<p class='error'>Error connecting to database</p>";
							break;
						case "viewError":
							echo "<p class='error'>You do not have permission to view this set</p>";
							break;
						case "editError":
							echo "<p class='error'>You do not have permission to edit this set</p>";
							break;
						case "noSID":
							echo "<p class='error'>No set ID!</p>";
							break;
						case "noGID":
							echo "<p class='error'>No game ID!</p>";
							break;
						case "termError":
							echo "<p class='error'>Error getting terms</p>";
							break;
					}
				}
			}
		?>

		<p><a href="/">Go home</a></p>

		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
