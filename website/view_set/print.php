<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/set_auth.php");

$set_query = "SELECT name FROM sets WHERE id='$set_id'";
$terms_query = "SELECT term, def FROM terms WHERE setid='$set_id'";

$set_res = $m->query($set_query)->fetch_all(MYSQLI_ASSOC);
$terms_res = $m->query($terms_query)->fetch_all(MYSQLI_ASSOC);

if (!$terms_res or !$set_res) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=termError"); //SNEAKY LIE- IF SET QUERY FAILS THEY WILL BE TOLD IT WAS TERM ERROR!
	exit();
}

$set_name = $set_res[0]["name"];

?>

<!DOCTYPE html>
<html>

<head>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title><?php echo $set_name; ?> | Revise it</title>

	<style type="text/css">

		.bold {
			font-weight:bold;
		}

	</style>

</head>

<body>

	<div id="content">

		<?php

			echo "<h1>$set_name</h1>";

			foreach ($terms_res as $i) {
				echo "<p><span class='bold'>$i[term]</span>: $i[def]</p>";
			}

		?>

	</div>
</body>

</html>
