<?php

if (isset($_POST["feedback"]) and isset($_POST["uid"])) {
	$date = date("d-m-Y H:i");
	$log = $date . " - From user " . $_POST["uid"] . ":\n" . $_POST["feedback"] . "\n\n";
	file_put_contents("/home/joe/reviseit-feedback.txt", $log, FILE_APPEND);

	echo $log;
}

header("Location: http://$_SERVER[HTTP_HOST]/?thanks");

?>
