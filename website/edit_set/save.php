<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/set_owner_auth.php");

$errors = "";

if (isset($_POST["sid"])) {

	$set_id = mysqli_real_escape_string($m, $_POST["sid"]);

	//REMOVE TERMS
	if (isset($_POST["to_remove_IDs"])) {

		$query = "DELETE FROM terms WHERE ";

		foreach ($_POST["to_remove_IDs"] as $i) {
			$id = mysqli_real_escape_string($m, $i);
			$query .= "id=$id OR ";
		}

		//REMOVE "OR "
		$query = substr($query, 0, -3);

		$res = $m->query($query);

		if (!$res) {
			$errors .= "removing,";
		}

	}

	//ADD TERMS
	if (isset($_POST["new_terms"]) and isset($_POST["new_defs"]) and count($_POST["new_terms"]) == count($_POST["new_defs"])) {

		$query = "INSERT INTO terms VALUES";

		for ($i=0;$i<count($_POST["new_terms"]);$i++) {
			$term = mysqli_real_escape_string($m, $_POST["new_terms"][$i]);
			$def = mysqli_real_escape_string($m, $_POST["new_defs"][$i]);

			$query .= "(null,'$set_id','$term','$def'), ";
		}

		//NEED TO REMOVE LAST ", "
		$query = substr($query, 0, -2);

		$res = $m->query($query);

		if (!$res) {
			$errors .= "adding,";
		}
	}
	
	//EDIT TERMS
	if (isset($_POST["edited_IDs"]) and isset($_POST["edited_terms"]) and isset($_POST["edited_defs"]) and 
		count($_POST["edited_terms"]) == count($_POST["edited_defs"]) and
		count($_POST["edited_IDs"]) == count($_POST["edited_defs"])) {

		$query = "INSERT INTO terms (id,term,def) VALUES ";

		for ($i=0;$i<count($_POST["edited_IDs"]);$i++) {
			$id = mysqli_real_escape_string($m, $_POST["edited_IDs"][$i]);
			$term = mysqli_real_escape_string($m, $_POST["edited_terms"][$i]);
			$def = mysqli_real_escape_string($m, $_POST["edited_defs"][$i]);

			$query .= "($id,'$term','$def'), ";
		}

		//REMOVE ", "
		$query = substr($query, 0 ,-2);

		$query .= " ON DUPLICATE KEY UPDATE term=VALUES(term), def=VALUES(def)";

		$res = $m->query($query);

		if (!$res) {
			$errors .= "editing,";
		}
	}

	//SET PRIVACY SETTING
	if (isset($_POST["privacy"])) {

		$privacy = mysqli_real_escape_string($m, $_POST["privacy"]);

		$query = "UPDATE sets SET privacy='$privacy' WHERE id='$set_id'";
		$res = $m->query($query);

		if (!$res) {
			$errors .= "privacySetting";
		}
	}

	//DELETE ALL CURRENT USERS IN PRIVACY LINK...
	$delete_query = "DELETE FROM privacy_link WHERE setid='$set_id'";
	$delete_res = $m->query($delete_query);

	if ( isset($_POST["sharing_with_IDs"]) and $delete_res ) {

		//...AND ADD THE UPDATED ONES
		$query = "INSERT INTO privacy_link VALUES ";

		foreach ($_POST["sharing_with_IDs"] as $i) {
			$user_id = mysqli_real_escape_string($m, $i);
			$query .= "('$set_id','$user_id'), ";
		}

		//REMOVE ", "
		$query = substr($query, 0, -2);

		$query .= " ON DUPLICATE KEY UPDATE userid=VALUES(userid)";
		$res = $m->query($query);

		if (!$res) {
			$errors .= "privacyList";
		}
	}
	else if (!$delete_res) {
		$errors .= "privacyList";
	}

	//DELETE ALL CURRENT TAGS IN TAG LINK... (V SIMILIAR TO PRIVACY ABOVE)
	$delete_tag_query = "DELETE FROM tag_link WHERE setid='$set_id'";
	$delete_tag_res = $m->query($delete_tag_query);

	if ( isset($_POST["tag_IDs"]) and $delete_tag_res ) {
		$query = "INSERT INTO tag_link VALUES ";
		foreach ($_POST["tag_IDs"] as $i) {
			$tag_id = mysqli_real_escape_string($m, $i);
			$query .= "('$set_id','$tag_id'), ";
		}
		$query = substr($query, 0, -2);
		$query .= " ON DUPLICATE KEY UPDATE tagid=VALUES(tagid)";
		$res = $m->query($query);

		if (!$res) {
			$errors .= "tagList";
		}
	}
	else if (!$delete_tag_res) {
		$errors .= "tagList";
	}

	//UPDATE 'LAST EDITED' FIELD
	$date = date("Y-m-d H:i:s");
	$query = "UPDATE sets SET edited='$date' WHERE id='$set_id'";
	$res = $m->query($query);
	if (!$res) {
		$errors .= "editTime";
	}
	
	//EDIT SET NAME
	if (isset($_POST["set_name"])) {
		$name = mysqli_real_escape_string($m, $_POST["set_name"]);
		$query = "UPDATE sets SET name='$name' WHERE id='$set_id'";
		$res = $m->query($query);
		if (!$res) {
			$errors .= "editName";
		}
	}

}

header("Location: http://$_SERVER[HTTP_HOST]/view_set/?sid=$set_id" . ($errors != "" ? "&e=$errors" : "") );
exit();

?>
