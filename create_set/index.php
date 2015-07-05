<?php 

include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php"); 

if (isset($_POST["set_name"])) {
	
	$date = date("Y-m-d H:i:s");
	$name = mysqli_real_escape_string($m, $_POST["set_name"]);

	$query = "INSERT INTO sets VALUES (null,$user_id,'none','$date','$date','$name')";
	$res = $m->query($query);

	if ($res) {
		$set_id = $m->insert_id;

		$query = "INSERT INTO terms VALUES (null,$set_id,'New term','New definition')";
		$res2 = $m->query($query);

		if ($res2) {
			header("Location: http://$_SERVER[HTTP_HOST]/edit_set/?sid=$set_id");
			exit();
		}
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>Create set | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="/scripts/validate.js"></script>
	<script type="text/javascript">

		$(document).ready(function(){
			$("form").submit(function(){
				return validate({
					presence: $("#set_name_box")
				});
			});
		});

	</script>

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>
	
	<div id="content">

		<h2>Create set</h2>

		<form action="#" method="POST">

		<?php

			//IF FORM WAS SUBMITTED AND STILL ON THIS PAGE THEN CREATING SET WAS NOT SUCCESSFUL.
			if (isset($_POST["set_name"])) {
				echo "<p class='error'>Error creating set (do you already have a set with this name?)</p>";
			}

		?>

			<p><label for="set_name_box">Name:</label> <input type="text" id="set_name_box" name="set_name" /></p>
			<p><button>Create</button></p>
		</form>
	
		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
