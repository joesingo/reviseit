<?php

if (isset($_POST["username"]) and isset($_POST["password"])) {
	include ("$_SERVER[DOCUMENT_ROOT]/includes/database.php");
	
	//FLAGS FOR ANY ERRORS
	$db_error = false;

	//IF USER IS NOT REDIRECTED FROM THIS PAGE IT IS MOST LIKELY THAT THEIR LOGIN CREDENTIALS WERE NOT CORRECT,
	//SO ASSUME THERE HAS BEEN TO AVIOD LOADS OF ELSES DOING $login_error = true EVERYWHERE
	//(ALTHOUGH IT COULD BE DB ACCESS ERROR)
	$login_error = true;
	
	$username = mysqli_real_escape_string($m, $_POST["username"]);
	$password = mysqli_real_escape_string($m, $_POST["password"]);
	
	$query = "SELECT id, password FROM users WHERE username='$username'";
	$res = $m->query($query)->fetch_array(MYSQL_ASSOC);
		
	if (count($res) != 0) {
	
		$id = $res["id"];
		$hash = $res["password"];
		
		if ( crypt($password,'$1$2$3') == $hash ) {
		
			$time = microtime(true);
			$query = "INSERT INTO sessions VALUES ($id, $time) ON DUPLICATE KEY UPDATE time=VALUES(time)";
			
			$res = $m->query($query);
			
			if ($res) {
				session_start();
				$_SESSION["time"] = $time;
				$_SESSION["id"] = $id;

				if (isset($_POST["remember_me"])) {
					$expire = time() + 365*24*60*60; //EXPIRE IN 365 DAYS
					setcookie("id",$id,$expire,"/");
					setcookie("time",$time,$expire,"/");
				}

				//REDIRECT TO PAGE THEY WERE TRYING TO GET TO OR HOME PAGE
				$url = "http://$_SERVER[HTTP_HOST]";
				if (isset($_POST["page"])) {
					$url .= $_POST["page"];
				}
				header("Location: $url");
				exit();
			}
			else {
				//ERROR INSERTING INTO DB
				$db_error = true;

				//NOT LOGIN ERROR AFTER ALL!
				$login_error = false;
			}
		}
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>Login | Revise it</title>

	<link rel="stylesheet" type="text/css" href="../styles/main_style.css" />
	<style type="text/css">

	</style>

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="/scripts/validate.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("form").submit(function(e){
				return validate({
					presence: $("input[name=username], input[name=password]"),
				});
			});
		});
	</script>

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/no_link_header.php"); ?>
	
	<div id="content">
		
		<h2>Log in</h2>
		<form action="/login/index.php" method="POST">
			
			<?php
				
				if (isset($_GET["nli"])) {
					echo "<p class='error'>You must be logged in to access this page</p>";
				}

				if (isset($login_error) and $login_error == true) {
					echo "<p class='error'>Incorrect username/password</p>";
				}
				
				if (isset($db_error) and $db_error == true) {
					echo "<p class='error'>Error logging in :(</p>";
				}

				if (isset($_GET["page"]) or isset($_POST["page"])) {
					$page = isset($_GET["page"]) ? $_GET["page"] : $_POST["page"];
					echo "<input type='hidden' name='page' value='$page' />";
				}
			
			?>

			<p><input type="text" name="username" placeholder="Username" /></p>
			<p><input type="password" name="password" placeholder="Password" /></p>
			<p><label>Remember me <input type="checkbox" name="remember_me" value="yessir" /></label></p>
			<p><button type="submit">Log in</button></p>
		</form>

		<p>
			(or <a href="/create_account" title="Create account">create account</a>)
		</p>
		
		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
