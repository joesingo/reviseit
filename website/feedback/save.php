<?php

if (isset($_POST["feedback"]) and isset($_POST["uid"])) {
	include("$_SERVER[DOCUMENT_ROOT]/includes/database.php");
	// include("$_SERVER[DOCUMENT_ROOT]/includes/email.php");

	$user_id = mysqli_real_escape_string($m, $_POST["uid"]);
	$query = "SELECT username FROM users WHERE id='$user_id'";
	$res = $m->query($query)->fetch_array(MYSQLI_ASSOC);

	$username = "User " . $user_id;
	if (count($res) != 0) {
		$username = $res["username"];
	}

	$date = date("d-m-Y H:i");
	$log = $date . " - From user " . $username . ":\n" . $_POST["feedback"] . "\n\n";
	file_put_contents("../../feedback.txt", $log, FILE_APPEND);

	$subject = "Reviseit feedback from " . $username;
	// send_email($subject, $log);
}

header("Location: http://$_SERVER[HTTP_HOST]/?thanks");

?>
