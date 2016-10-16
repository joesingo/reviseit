<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/database.php");

session_start();
$q = mysqli_real_escape_string($m, $_POST["q"]);
$user_id = mysqli_real_escape_string($m, $_SESSION["id"]);

$query = "SELECT id, username FROM users WHERE username LIKE '$q%' AND id<>'$user_id' ORDER BY username ASC";
$res = $m->query($query)->fetch_all(MYSQLI_ASSOC);

if ($res) {
	echo "<ul>";
	foreach ($res as $i) {
		echo "<li><a data-id='$i[id]'>$i[username]</a></li>";
	}
	echo "</ul>";
}
else {
	echo "No results for $q";
}

?>