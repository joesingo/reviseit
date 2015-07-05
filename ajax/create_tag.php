<?php

//CREATES A TAG AND ECHOES THE HTML TO GO ON PAGE IN edit_set/index.php

include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php");

if (isset($_POST["name"])) {
	$name = mysqli_real_escape_string($m, $_POST["name"]);
	$query = "INSERT INTO tags VALUES (null, '$name');";
	
	$res = $m->query($query);
	
	if ($res) {
		$id = $m->insert_id;
		
		echo "<a data-id='$id'>$name</a>";
	}
	else {
		echo "Error";
	}
}
else {
	echo "Error";
}

?>
