<?php
session_start();

include("$_SERVER[DOCUMENT_ROOT]/includes/database.php");

if (isset($_SESSION["time"]) and isset($_SESSION["id"])) {

	$id = mysqli_real_escape_string($m, $_SESSION["id"]);
	$time = mysqli_real_escape_string($m, $_SESSION["time"]);

	$query = "DELETE FROM sessions WHERE time='$time' AND userid=$id";
	$res = $m->query($query);
}

if (isset($_COOKIE["time"])) {
	setcookie("time","",time() - 3600);
	unset($_COOKIE["time"]);
}

if (isset($_COOKIE["id"])) {
	setcookie("id","",time() - 3600);
	unset($_COOKIE["id"]);
}

session_destroy();

header("Location: http://$_SERVER[HTTP_HOST]/login/index.php");
exit();

?>