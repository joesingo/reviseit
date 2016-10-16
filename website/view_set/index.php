<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/set_auth.php");
include("$_SERVER[DOCUMENT_ROOT]/includes/date.php");

$set_query = "SELECT sets.name, sets.created, sets.edited, sets.userid, count(terms.id) AS termcount FROM sets JOIN terms ON (terms.setid=sets.id) WHERE sets.id='$set_id' GROUP BY sets.id";
$terms_query = "SELECT term, def FROM terms WHERE setid='$set_id'";
$games_query = "SELECT id, name, path, save_scores FROM games";

$set_res = $m->query($set_query)->fetch_all(MYSQLI_ASSOC);
$terms_res = $m->query($terms_query)->fetch_all(MYSQLI_ASSOC);
$games_res = $m->query($games_query)->fetch_all(MYSQLI_ASSOC);

if (!$set_res or !$terms_res) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php");
}

?>

<!DOCTYPE html>
<html>

<head>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>View '<?php echo $set_res[0]["name"]; ?>' | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />
	<style type="text/css">

		#list dl {
			display:table;
			margin:auto;
		}

		#list dt, #list dd {
			display:table-cell;
			padding:5px 6px;
			width:400px;
		}

		#list dt {
			text-align:right;
		}

		#list dd {
			text-align:left;
		}

		#list dl p {
			text-align:center;
			margin:0;
		}

		#games li {
			list-style:none;
		}

		#games ul {
			padding:0;
		}

		#games table {
			margin:auto;
		}

		#games td {
			padding:10px;
		}

		#games table img {
			width: 100px;
			height: 75px;
		}

		#export {
			display:none;
		}

		#export textarea {
			width:80%;
			height:180px;
		}

		#export_seperator {
			width:30px;
		}

		#pre_export_rule {
			display:none;
		}

		#scores_wrapper {
			display:none;
		}

		.correct {
			color:green;
		}

		.incorrect {
			color:red;
		}

	</style>

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="/scripts/graph.js"></script>

</head>

<body>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>

	<div id="content">

		<h2>View Set</h2>

		<h3><?php echo $set_res[0]["name"]; ?></h3>

		<?php

			//ERRORS COMING FROM edit_set/save.php
			if (isset($_GET["e"])) {
				$errors = explode(",",$_GET["e"]);
				foreach ($errors as $i) {
					switch ($i) {
						case "removing":
							echo "<p class='error'>Error removing terms</p>";
							break;
						case "adding":
							echo "<p class='error'>Error adding terms</p>";
							break;
						case "editing":
							echo "<p class='error'>Error editing terms</p>";
							break;
						case "privacySetting":
							echo "<p class='error'>Error updating privacy settings</p>";
							break;
						case "privacyList":
							echo "<p class='error'>Error editing privacy list</p>";
							break;
						case "tagList":
							echo "<p class='error'>Error editing tag list</p>";
							break;
						case "editTime":
							echo "<p class='error'>Error updating edit time</p>";
							break;
						case "tooFewTerms":
							echo "<p class='error'>You need to have " . (isset($_GET["termsRequired"]) ? $_GET["termsRequired"] : "more") . " terms to play this game!</p>";
							break;
						case "editName":
							echo "<p class='error'>Error editing set name</p>";
							break;
					}
				}
			}
		?>

		<p class="small">
			Created: <?php echo get_date($set_res[0]["created"]); ?>
			<br />
			Last edited: <?php echo get_date($set_res[0]["edited"]); ?>
			<br />
			<?php echo $set_res[0]["termcount"]; ?> terms
		</p>

		<section id="list">
			<dl>
				<?php
					foreach ($terms_res as $i) {
						echo "<div><dt>$i[term]:</dt><dd>$i[def]</dd></div>";
					}
				?>
			</dl>
		</section>

		<?php
			if ($set_res[0]["userid"] == $user_id) {
				echo "<p><a href='/edit_set/?sid=$set_id'>Edit</a></p>";
			}

			echo "<p><a href='/view_set/print.php?sid=$set_id'>Printable page</a></p>";
		?>

		<p><a href="#" id="show_export_link">Export terms</a></p>

		<hr id="pre_export_rule" />

		<section id="export">
			<p><label>Seperator <input type="text" id="export_seperator" value=";" /></label></p>
			<textarea></textarea>
		</section>

		<hr />

		<h3>Games</h3>

		<section id="games">
			<?php

				if ($games_res) {
					echo "<table>";

					for ($i=0;$i<count($games_res);$i++) {

						if ($i%4 == 0) {
							if ($i != 0) {
								echo "</tr>";
							}
							echo "<tr>";
						}

						$a_start_tag = "<a href='/games/?gid=" . $games_res[$i]["id"] . "'>";
						echo "<td>$a_start_tag<img class='tile' src='/images/screenshots/" . $games_res[$i]["path"] . ".png' /></a>";
						echo "<br />";
						echo $a_start_tag . $games_res[$i]["name"] . "</a></td>";
					}

					echo "</table>";
				}
				else {
					echo "Error getting games list";
				}

			?>
		</section>

		<hr />

		<h3>Scores</h3>

		<section id="scores">

			<?php
				if ($games_res) {
					echo "<select id='games_dropdown'>";
					echo "<option value='nothing'>Select a game</option>";
					foreach ($games_res as $i) {
						if ($i["save_scores"] == 1) {
							echo "<option value='$i[id]'>$i[name]</option>";
						}
					}
					echo "</select>";
				}
			?>

			<div id="scores_wrapper">
				<p id="stats_p"></p>

				<h4>Graph of your scores over time:</h4>
				<div id="graph_wrapper"></div>

				<h4>Highscores</h4>
				<div id="highscores_div"></div>
			</div>

			<script type="text/javascript">

				$("#games a").each(function() {
					var href = $(this).attr("href") + "&sid=<?php echo $set_id; ?>";
					$(this).attr("href",href);
				});

				var exportTerms = function() {
					var sep = $("#export_seperator").val();
					var e = "";

					$("#list dl div").each(function(){
						var term = $(this).find("dt").html().slice(0,-1); //REMOVE COLON AT THE END
						var def = $(this).find("dd").html();
						e += term + sep + def + sep;
					});

					$("#export textarea").val(e);
				};

				$("#show_export_link").click(function(){
					$(this).remove();
					$("#export, #pre_export_rule").show();
					exportTerms();
					return false;
				});

				$("#export_seperator").keyup(exportTerms);

				$("#games_dropdown").change(function(){
					if ($(this).val() == "nothing") {
						$("#scores_wrapper").hide();
						return false;
					}

					$("#scores_wrapper").show();

					//GET USER'S SCORES FOR GRAPH
					$.ajax({
						url: "/ajax/get_scores.php",
						method: "GET",
						data: {
							sid: <?php echo $set_id; ?>,
							gid: $(this).val()
						},
						beforeSend: function(){
							$("#graph_wrapper").html("Loading...");
						}
					}).done(function(res){
						if (res == "Error") {
							$("#graph_wrapper").html("Error getting scores");
						}
						else if (res == "No scores") {
							$("#graph_wrapper").html("No scores found!")
						}
						else {
							getGraph($.parseJSON(res),$("#graph_wrapper"));
						}
					});

					//GET HIGHSCORES
					$.ajax({
						method: "GET",
						url: "/ajax/get_highscores.php",
						data: {
							sid: <?php echo $set_id; ?>,
							gid: $(this).val(),
							limit: "10"
						},
						beforeSend: function(){
							$("#highscores_div").html("Loading...");
						}
					}).done(function(res){
						if (res == "Error") {
							$("#highscores_div").html("Error getting highscores");
						}
						else if (res == "No scores") {
							$("#highscores_div").html("No scores found!");
						}
						else {
							var scores = $.parseJSON(res);
							$("#highscores_div").html("<ol></ol>");
							for (var i=0;i<scores.length;i++) {
								$("#highscores_div ol").append("<li><span class='bold'>" + scores[i].score + "</span> - " + scores[i].user + "</li>");
							}
						}
					});
				});

			</script>

		</section>

		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>

	</div>
</body>

</html>
