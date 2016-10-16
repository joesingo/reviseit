<?php

//OUTPUTS LIST OF TAGS FROM SEARCH

include("$_SERVER[DOCUMENT_ROOT]/includes/database.php");

$q = mysqli_real_escape_string($m, $_POST["q"]);
$query = "SELECT id, name FROM tags WHERE name LIKE '$q%' ORDER BY name ASC";

$res = $m->query($query)->fetch_all(MYSQLI_ASSOC);

if ($res) {
	echo "<ul>";
	foreach ($res as $i) {
		echo "<li><a data-id='$i[id]'>$i[name]</a></li>";
	}
	echo "</ul>";
}
else {
	echo "No results";
}

?>
