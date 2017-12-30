<?php

$m = new mysqli("db","root","reviseitroot","reviseit");

if ($m->connect_error) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=dbError");
	exit();
}

?>
