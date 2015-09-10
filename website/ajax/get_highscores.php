<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/set_auth.php");

if (isset($_GET["gid"]) and isset($_GET["limit"])) {
	
	$game_id = mysqli_real_escape_string($m, $_GET["gid"]);
	$limit = mysqli_real_escape_string($m, $_GET["limit"]);

	$query = "SELECT username, score FROM scores JOIN users ON scores.userid=users.id WHERE setid='$set_id' AND gameid='$game_id' ORDER BY score DESC LIMIT $limit";
	$res = $m->query($query)->fetch_all(MYSQL_ASSOC);

	if ($res) {
		echo "[";

		$output = "";
		foreach ($res as $i) {
			$output .= '{"user": "' . $i["username"] . '", "score": ' . $i["score"] . '},';
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