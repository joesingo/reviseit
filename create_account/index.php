<?php

if (isset($_POST["username"])) {
	include("$_SERVER[DOCUMENT_ROOT]/includes/database.php");

	//LIKE IN LOGIN FILE, USER WILL ONLY STAY ON THIS PAGE IF THERE WAS ERROR CREATING ACCOUNT,
	///SO SET IT TRUE HERE
	$error = true;

	$username = mysqli_real_escape_string($m, $_POST["username"]);
	$password = crypt( mysqli_real_escape_string($m, $_POST["password"]), '$1$2$3' );
	$email = mysqli_real_escape_string($m, $_POST["email"]);
	$name = mysqli_real_escape_string($m, $_POST["name"]);

	$query = "INSERT INTO users VALUES (null,'$username','$password','$name','$email')";
	$res = $m->query($query);
	if ($res) {
		header("Location: http://$_SERVER[HTTP_HOST]/login");
		exit();
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>
	
	<title>Create account | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />
	<style type="text/css">

		.table {
			display:table;
			margin:auto;
			table-layout:fixed;
		}

		.table p {
			display:table-row;
		}

		.table label {
			text-align:right;padding:10px;
		}

		.table label, .table input {
			display:table-cell;
		}

	</style>

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="/scripts/validate.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("form").submit(function(){
				return validate({
					presence: $("#username, input[type=password], #name"),
					match: $("input[type=password]")
				});
			});
		});
	</script>

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/no_link_header.php"); ?>
	
	<div id="content">
	
		<h2>Create Account</h2>
		<form action="#" method="POST">

			<?php

				if (isset($error) and $error == true) {
					echo "<p class='error'>Error creating account</p>";
				}

			?>

			<div class="table">
				<p><label for="username">Username:</label> <input type="text" id="username" name="username" /></p>
				<p><label for="password1">Password:</label> <input type="password" id="password1" name="password" /></p>
				<p><label for="password2">Confirm password:</label> <input type="password" id="password2" /></p>
				<p><label for="email">Email (optional):</label> <input type="email" id="email" name="email" /></p>
				<p><label for="name">Name:</label> <input type="text" id="name" name="name" /></p>
			</div>
			<p><button type="submit">Create account</button>
		</form>
	
		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
