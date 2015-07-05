<?php

//OUTPUTS LIST OF SETS FROM SEARCH

include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php");
include("$_SERVER[DOCUMENT_ROOT]/includes/date.php");

$select = "SELECT sets.id, sets.name, sets.created, sets.edited, count(terms.id) AS termcount";
$join = "JOIN terms ON (terms.setid=sets.id)";
$group_by = "GROUP BY sets.id";
$order_by = "ORDER BY sets.edited DESC";
$where = "";

$username_select = "$select, users.username";
$username_where = "sets.userid=users.id";
$username_join = "$join JOIN users ON (users.id=sets.userid)";
$tag_join = "";

if (isset($_GET["tag_q"])) {
	$tag_q = mysqli_real_escape_string($m, $_GET["tag_q"]);

	$tag_join .= " LEFT JOIN tag_link ON (tag_link.setid=sets.id) LEFT JOIN tags ON (tags.id=tag_link.tagid)";
	$where .= "AND tags.name LIKE '$tag_q%'";
}

if (isset($_GET["name_q"])) {
	$name_q = mysqli_real_escape_string($m, $_GET["name_q"]);

	$where .= ($where != "" ? " " : "") . "AND sets.name LIKE '$name_q%'";
}

$res = [];

if (isset($_GET["your_sets"])) {
	$your_sets_query = "$select FROM sets $join $tag_join WHERE sets.userid='$user_id' $where $group_by $order_by";
	$res[] = $m->query($your_sets_query)->fetch_all(MYSQL_ASSOC);
}

if (isset($_GET["shared_all"])) {
	$shared_all_query = "$username_select FROM sets $username_join $tag_join WHERE sets.privacy='all' AND sets.userid<>'$user_id' $where $group_by $order_by";
	$res[] = $m->query($shared_all_query)->fetch_all(MYSQL_ASSOC);
}

if (isset($_GET["shared_some"])) {
	$shared_some_query = "$username_select FROM sets $username_join $tag_join JOIN privacy_link ON (privacy_link.setid=sets.id AND privacy_link.userid='$user_id') WHERE sets.privacy='some' $where $group_by $order_by";
	$res[] = $m->query($shared_some_query)->fetch_all(MYSQL_ASSOC);
}

$sets_found = false;
$output = "";

foreach ($res as $i) {

	if (!$sets_found and count($i) > 0) {
		$sets_found = true;
	}

	foreach ($i as $j) {

		$created = get_date($j["created"]);
		$edited = get_date($j["edited"]);

		$output .= "<li>";
		$output .= "<h4><a href='/view_set/?sid=$j[id]'>$j[name]</a><br /></h4>";
		$output .= "Created: $created<br />";
		$output .= "Last edited: $edited<br />";
		$output .= "$j[termcount] terms<br />";

		$tag_query = "SELECT tags.name FROM tags, tag_link WHERE tag_link.setid=$j[id] AND tags.id=tag_link.tagid";
		$tag_res = $m->query($tag_query)->fetch_all(MYSQL_ASSOC);

		if ($tag_res) {
			$output .= "Tagged: ";
			foreach ($tag_res as $k) {
				$output .= $k["name"] . ", ";
			}
			$output .= "<br />";
		}

		if (isset($j["username"])) {
			$output .= "Created by $j[username]";
		}
		else {
			$output .= "<a href='/edit_set/?sid=$j[id]'>Edit</a>";
		}

		$output .= "<br /><a href='/games/?gid=1&sid=$j[id]'>Flashcards</a>";
		$output .= "</li>";
	}
}

if ($sets_found) {
	echo "<ul>$output</ul>";
}
else {
	echo "No sets found";
}

?>