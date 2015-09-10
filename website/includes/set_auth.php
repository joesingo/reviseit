<?php

//CHECK THIS USER HAS PERMISSIONS TO SEE/PLAY GAME ON THIS SET

include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php");

if (isset($_POST["sid"])) {
	$_GET["sid"] = $_POST["sid"];
}

if (!isset($_GET["sid"])) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=noSID");
	exit();
}

$set_id = mysqli_real_escape_string($m, $_GET["sid"]);

$query = "
	SELECT sets.id
	FROM sets
	LEFT JOIN privacy_link ON privacy_link.setid=sets.id
	WHERE sets.id='$set_id' 
	AND (
		sets.privacy='all' OR
		(sets.privacy='some' AND privacy_link.userid='$user_id') OR
		sets.userid='$user_id'
	)
";

$res = $m->query($query)->fetch_all();

if (count($res) < 1) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=viewError");
	exit();
}

?>