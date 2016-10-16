<?php

//RETURN JAVASCRIPT ARRAY OF SCORES AND DATES

include("$_SERVER[DOCUMENT_ROOT]/includes/set_auth.php");

if (isset($_GET["gid"])) {

	$game_id = mysqli_real_escape_string($m, $_GET["gid"]);

	$query = "SELECT date, score FROM scores WHERE userid='$user_id' AND setid='$set_id' AND gameid='$game_id'";
	$res = $m->query($query)->fetch_all(MYSQLI_ASSOC);

	if ($res) {
		echo "[";

		$output = "";
		foreach ($res as $i) {
			$output .= '{"date": "' . $i["date"] . '", "score": ' . $i["score"] . '},';
		}
		$output = substr($output, 0, -1);

		echo $output;
		echo "]";
	}
	else {
		echo "No scores";
	}
}
else {
	echo "Error";
}

?>