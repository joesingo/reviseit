<?php

//SAVE SCORE INTO SCORES TABLE

include("$_SERVER[DOCUMENT_ROOT]/includes/set_auth.php");

if (isset($_GET["gid"]) and isset($_GET["score"])) {
	
	$game_id = mysqli_real_escape_string($m, $_GET["gid"]);
	$score = mysqli_real_escape_string($m, $_GET["score"]);
	$date = date("Y-m-d H:i:s");

	$error = false;

	$score_query = "INSERT INTO scores VALUES ('$user_id','$set_id','$game_id','$score','$date')";
	if (!$m->query($score_query)) {
		$error = true;
	}

	if ($error) {
		echo "Error saving score";
	}
	else {
		echo "Successfuly saved score";
	}
}

?>