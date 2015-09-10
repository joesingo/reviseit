<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php");

if (isset($_POST["sid"])) {
	$_GET["sid"] = $_POST["sid"];
}

if (!isset($_GET["sid"])) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=noSID");
	exit();
}

$set_id = mysqli_real_escape_string($m, $_GET["sid"]);

$query = "SELECT userid FROM sets WHERE id='$set_id'";
$res = $m->query($query)->fetch_all(MYSQL_ASSOC);

if ($res) {
	if ($res[0]["userid"] != $user_id) {
		header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=editError");
		exit();
	}
}
else {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?badID");
	exit();
}

?>