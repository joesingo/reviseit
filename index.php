<?php include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php"); ?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>
	
	<title>Home | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>
	
	<div id="content">

		<?php
			$query = "SELECT name FROM users WHERE id='$user_id'";
			$res = $m->query($query)->fetch_all(MYSQL_ASSOC);

			echo "<h2>Hello " . ($res ? $res[0]["name"] : "") . "</h2>";
		?>

		<div class="big">

			<p>Welcome to Revise It</p>

			<p>
				Click <a href="/view_sets">view sets</a> to view your sets and sets that have been shared with you,
				or <a href="/create_set">create a set</a>.
			</p>

			<p>
				From the view set page you can play games and quizzes, check your progress,
				see highscores for that set and edit terms.
			</p>
		</div>

		<hr />
		<div class="small">
			<p>This site was made for my coursework project for A level computing. You can leave feedback <a href="/feedback">here</a></p>
		</div>

		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
	</div>
</body>

</html>
