<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/auth.php");

?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>View sets | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />
	<style type="text/css">

		ul {
			padding:0;
		}

		li {
			list-style:none;
			padding-bottom:20px;
		}

		li h4 {
			margin:5px;
			font-size:1.2em;
		}

	</style>

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript">

		function getResults() {

			var data = {};

			$("#filter_area input[type=checkbox]:checked").each(function(){
				data[ $(this).data("filter") ] = 1;
			});

			if ( $("#name_q").val() != "" ) {
				data["name_q"] = $("#name_q").val();
			}

			if ( $("#tag_q").val() != "" ) {
				data["tag_q"] = $("#tag_q").val();
			}

			$.ajax({
				url: "/ajax/set_search.php",
				method: "GET",
				data: data,
				beforeSend: function() {
					$("#results_area").html("Loading...");
				}
			}).done(function(res){
				$("#results_area").html(res);
			});
		}

		$(document).ready(function(){
			getResults();
			$("#filter_area input[type=checkbox]").change(getResults);
			$("#filter_area input[type=text]").keyup(getResults);
		});

	</script>

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>
	
	<div id="content">

		<h2>View sets</h2>

		<section id="filter_area">
			<label>View your sets <input type="checkbox" data-filter="your_sets" checked /></label><br />
			<label>View sets shared with you <input type="checkbox" data-filter="shared_some" /></label><br />
			<label>View public sets <input type="checkbox" data-filter="shared_all" /></label><br />
			<p><input type="text" id="name_q" placeholder="Search by set name" /></p>
			<p><input type="text" id="tag_q" placeholder="Search by tags" /></p>
		</section>

		<hr />
		
		<section id="results_area"></section>
	
		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
		
	</div>
</body>

</html>
