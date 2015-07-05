<?php
function get_date($d) {
	//RETURN NICELY FORMATTED DATE GIVEN DATE FROM MYSQL TABLE
	return substr($d,8,2) . "-" . substr($d,5,2) . "-" . substr($d,0,4) . " " . substr($d,11,2) . ":" . substr($d,14,2);
}
?>