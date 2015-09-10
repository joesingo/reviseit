<?php

//CHECKS THAT USER IS LOGGED IN

include("$_SERVER[DOCUMENT_ROOT]/includes/database.php");

$redirect_url = "http://$_SERVER[HTTP_HOST]/login/?nli&page=" . urlencode($_SERVER["REQUEST_URI"]);

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (isset($_SESSION["time"]) and isset($_SESSION["id"])) {
	$time = mysqli_real_escape_string($m, $_SESSION["time"]);
	$user_id = mysqli_real_escape_string($m, $_SESSION["id"]);
}
else if (isset($_COOKIE["time"]) and isset($_COOKIE["id"])) {
	$time = mysqli_real_escape_string($m, $_COOKIE["time"]);
	$user_id = mysqli_real_escape_string($m, $_COOKIE["id"]);

	//SET SESSIONS VARS IF USING COOKIE
	$_SESSION["time"] = $time;
	$_SESSION["id"] = $user_id;
}
else {
	header("Location: $redirect_url");
	exit();
}

$query = "SELECT time FROM sessions WHERE time='$time' AND userid=$user_id";
$res = $m->query($query)->fetch_all(MYSQL_ASSOC);

if (count($res) != 1) {
	header("Location: $redirect_url");
	exit();
}

?>