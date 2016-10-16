<?php

include("$_SERVER[DOCUMENT_ROOT]/includes/set_owner_auth.php");

$terms_query = "SELECT id, term, def FROM terms WHERE setid='$set_id'; ";
$set_query = "SELECT sets.name, sets.privacy, sets.userid, sets.created, sets.edited, count(terms.id) AS termcount FROM sets JOIN terms ON (terms.setid=sets.id) WHERE sets.id='$set_id' GROUP BY sets.id";
$privacy_query = "SELECT privacy_link.userid, users.username FROM privacy_link, users WHERE privacy_link.setid='$set_id' AND privacy_link.userid=users.id";
$tags_query = "SELECT tags.name, tag_link.tagid FROM tags, tag_link WHERE tag_link.setid='$set_id' AND tag_link.tagid=tags.id";

$terms_res = $m->query($terms_query)->fetch_all(MYSQLI_ASSOC);
$set_res = $m->query($set_query)->fetch_all(MYSQLI_ASSOC);
$privacy_res = $m->query($privacy_query)->fetch_all(MYSQLI_ASSOC);
$tags_res = $m->query($tags_query)->fetch_all(MYSQLI_ASSOC);

if (!$terms_res or !$set_res) {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=badID");
	exit();
}

include("$_SERVER[DOCUMENT_ROOT]/includes/date.php")

?>

<!DOCTYPE html>
<html>

<head>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title>Edit '<?php echo $set_res[0]["name"]; ?>' | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />
	<style type="text/css">

		#term_def_area {
			display:table;
			margin:auto;
			position:relative;
		}

		#term_def_area > div {
			display:table-row;
		}

		#term_def_area > div > div {
			display:table-cell;
			padding:8px 3px;
		}

		#term_def_area > div > div:first-child input {
			text-align:right;
		}

		#term_def_area > div > div:last-child input {
			text-align:left;
		}

		#term_def_area input {
			width:200px;
		}

		#term_def_area button {
			position:absolute;
			left:100%;
			margin-left:10px;
			font-size:0.6em;
			margin-top:8px;
			width:auto;
		}

		#settings_area > p label {
			margin:8px;
		}
		#settings_area > p label input {
			margin:auto 5px;
		}

		#sharing_div ul, #tag_div ul {
			list-style:none;
			padding:0;
		}

		#sharing_div li, #tag_div li {
			padding:4px;
		}

		#sharing_div a, #tag_div a {
			cursor:pointer;
		}

		#sharing_with_span a::after, #current_tags_span a::after {
			content:", ";
		}

		#pop_up_textarea {
			position:absolute;
			width:275px;
			height:200px;
		}

		body {
			position:relative; /*FOR POP UP TEXTAREA*/
		}

		button {
			width:150px;
		}

		#import_area textarea {
			width:80%;
			height:180px;
		}

		#import_separator_box {
			width:30px;
		}

		@media screen and (max-width: 550px) {

			#term_def_area > div > div {
				display:block;
			}

			#term_def_area > div > div:first-child input {
				text-align:left;
			}

			#term_def_area button {
				position:static;
			}

		}

	</style>

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="/scripts/validate.js"></script>
	<script type="text/javascript">

		var addFormField = function(name,value) {
			$("form").append("<input type='hidden' name='" + name + "' value='" + value + "' />");
		}

		var addNewTerm = function(term,def) {
			var s = "<div data-new='true'>";
			s += "<div><input type='text' value='" + term + "' /> :</div>";
			s += "<div><input type='text' value='" + def + "' /></div>";
			s += "<button title='Remove term'>X</button>";
			s += "</div>";

			$("#term_def_area").append(s);
			$("#term_def_area > div[data-new=true] button").off().click(function(){
				removeTerm( $(this).parent() );
			})

			//PUT FOCUS IN NEW TERM BOX
			$("#term_def_area > div:last-child > div:first-child input").focus();
		};

		var removeTerm = function(el) {
			//el IS THE DIV THAT CONTAINS TERM AND DEF INPUTS

			if ( $("#term_def_area > div").length == 1 ) {
				//CANNOT REMOVE TERM IF IT IS THE ONLY ONE LEFT
				$("#remove_error").show();
				return false;
			}
			else {
				//HIDE ERROR IN CASE IT WAS SHOWN BEFORE
				$("#remove_error").hide();

				//IF IT WAS NOT A NEW TERM...
				if ($(el).is("[data-id]")) {
					var id = $(el).attr("data-id");
					addFormField("to_remove_IDs[]",id);
				}

				//REMOVE FROM DOM
				$(el).remove();
				return true;
			}
		};

		//CREATE A TAG AND ADD IT TO CURRENT TAGS LIST
		var createTag = function(name) {
			$.ajax({
				url: "/ajax/create_tag.php",
				method: "POST",
				data: {
					name: name
				}
			}).done(function(res){
				if (res == "Error") {
					$("#tag_search_results_area").html("Error creating tag :(");
				}
				else {
					$("#current_tags_span").append(res);
					$("#tag_div input").val("");
					$("#tag_search_results_area").html("");
				}
			});
		};

		$(document).ready(function(){

			//HIDE THINGS
			$("#import_area, #settings_area, #remove_error, #sharing_div, #pop_up_textarea").hide();

			//HAVE TEXTAREA POP UP ON DOUBLE CLICK FOR EASIER EDITING OF LONG TERMS/DEFS
			$(document).on("dblclick","#term_def_area input",function(e){

				//PUT TERM/DEF IN TEXT AREA, POSITION OVER TEXTBOX, SHOW IT AND GIVE IT FOCUS
				$("#pop_up_textarea").val( $(this).val() ).css({
					left: e.pageX,
					top: e.pageY
				}).show().focus();

				var t = this;

				//PUT EDITED TERM BACK IN TEXT BOX AND HIDE TEXTAREA WHEN TEXTAREA LOSES FOCUS
				$("#pop_up_textarea").off().blur(function(){
					$(t).val( $(this).val() );
					$(t).change(); //NEED TO DO THIS SO THAT EDITED FLAG IS SET
					$(this).hide();
				});
			});

			//MAKE IMPORT BUTTON WORK
			$("#import_button").click(function(){
				$(this).hide();
				$("#import_area").show();
			});
			$("#import_separator_box").val(";");

			//MAKE SETTINGS BUTTON WORK
			$("#settings_button").click(function(){
				$(this).hide();
				$("#settings_area").show();
			});

			//MAKE PRIVACY RADIO BUTTONS WORK
			$("input[name=privacy]").click(function(){
				privacy = $(this).val();

				if (privacy == "some") {
					$("#sharing_div").show();
				}
				else {
					$("#sharing_div").hide();
				}
			});

			//MAKE CORRECT PRIVACY BOX CHECKED WHEN PAGE LOADS
			var privacy = "<?php echo $set_res[0]['privacy']; ?>";
			$("input[value=" + privacy + "]").click();

			//MAKE EDIT SET NAME LINK WORK
			$("#edit_set_name_link").click(function(){
				$("#set_name_wrapper").attr("data-edited","true");

				var currentName = $("#set_name_wrapper h3").html();
				$("#set_name_wrapper").html("<input type='text' />");
				$("#set_name_wrapper input").val(currentName).focus().blur(function(){
					$(this).parent().html("<h3>" + $(this).val() + "</h3>");
				});
			});

			//SEARCH FOR USERS
			$("#sharing_div input").keyup(function(){

				//AJAX TO GET SEARCH RESULTS
				$.ajax({
					url: "/ajax/user_search.php",
					method: "POST",
					data: {
						q: $(this).val()
					},
					beforeSend: function(){
						$("#user_search_results_area").html("Loading...")
					}
				}).done(function(res){

					//SHOW RESULTS
					$("#user_search_results_area").html(res);

					//HANDLE CLICK
					$("#user_search_results_area a").click(function(){
						var id = $(this).attr("data-id");

						//IF USER IS NOT ALREADY IN LIST...
						if ($("#sharing_with_span a[data-id=" + id + "]").length == 0) {

							//ADD THEM TO LIST!
							$("#sharing_with_span").append(
								$(this).parent().html()
							);
						}

						//REMOVE FROM DOM
						$(this).parent().remove();

						//EMPTY BOX
						$("#sharing_div input").val("");
					});
				});
			});

			//SEARCH FOR TAGS
			$("#tag_div input").keyup(function(){
				var searchQuery = $(this).val();

				$.ajax({
					url: "/ajax/tag_search.php",
					method: "POST",
					data: {
						q: searchQuery
					},
					beforeSend: function(){
						$("#tag_search_results_area").html("Loading...")
					}
				}).done(function(res){

					//IF NO RESULTS OFFER TO CREATE TAG
					if (res == "No results") {
						var html = "<p>No results for " + searchQuery + "</p><p>Do you want to <a id='create_tag_link'>create a tag for " + searchQuery + "</a>?</p>";
						$("#tag_search_results_area").html(html).find("#create_tag_link").click(function(){
							createTag(searchQuery);
						});
					}
					else {
						$("#tag_search_results_area").html(res);
						$("#tag_search_results_area a").click(function(){
							var id = $(this).attr("data-id");
							if ($("#current_tags_span a[data-id=" + id + "]").length == 0) {
								$("#current_tags_span").append(
									$(this).parent().html()
								);
							}
							$(this).parent().remove();
							$("#tag_div input").val("");
						});
					}
				});
			});

			//TO REMOVE USERS FOR SHARING WITH LIST AND REMOVE TAGS FROM LIST
			$(document).on("click","#sharing_with_span a, #current_tags_span a",function(){
				$(this).remove();
			})

			//SET EDITED FLAG WHEN TERM OR DEF IS CHANGED
			$("#term_def_area > div > div input").change(function(){
				$(this).parent().parent().attr("data-edited","true");
			});

			//REMOVE TERM
			$("#term_def_area button").click(function(){
				removeTerm( $(this).parent() );
			});
			//ADD NEW TERM
			$("#add_term_button").click(function(){
				addNewTerm("","");
			});
			//ADD NEW TERM WHEN RETURN IS PRESSED IN DEF BOX
			$(document).on("keypress","#term_def_area > div > div:nth-child(2) input",function(e) {
				if (e.keyCode == 13) {
					addNewTerm("","");
				}
			});

			//IMPORT TERMS
			$("#import_area button").click(function(){
				if (validate({presence: $("#import_separator_box")})) {

					var sep = $("#import_separator_box").val();
					var split = $("#import_area textarea").val().replace(/['"]/g,"").split(sep);

					//REMOVE LAST ONE IF THERE IS ODD NUMBER OF TERM/DEFS
					if (split.length % 2 == 1) {
						split.splice(-1,1);
					}

					for (var i=0;i<split.length;i+=2) {
						addNewTerm(split[i], split[i+1]);
					}

					$("#import_area textarea").val("");
				}
			});

			//SAVE - SUBMIT FORM
			$("form").submit(function(){

				if ( !validate({presence: $("#term_def_area input") }) ) {
					return false;
				}

				//REMOVE QUOTES FROM ANY TERMS
				$("#term_def_area input").each(function(){
					$(this).val(
						$(this).val().replace(/['"]/g,"")
					);
				});

				//NEW TERMS
				$("#term_def_area > div[data-new=true]").each(function(){
					var term = $(this).find("div:first-child input").val();
					var def = $(this).find("div:nth-child(2) input").val();

					addFormField("new_terms[]",term);
					addFormField("new_defs[]",def);
				})

				//EDITED TERMS
				$("#term_def_area > div[data-edited=true]").each(function(){
					var id = $(this).attr("data-id");
					var term = $(this).find("div:first-child input").val();
					var def = $(this).find("div:nth-child(2) input").val();

					addFormField("edited_IDs[]",id);
					addFormField("edited_terms[]",term);
					addFormField("edited_defs[]",def);
				})

				//EDITED SET NAME (IF EDITED)
				if ($("#set_name_wrapper").attr("data-edited") === "true") {
					var newName = $("#set_name_wrapper h3").html();
					addFormField("set_name",newName);
				}

				//PRIVACY SETTING
				addFormField("privacy",privacy);

				//PUT USERS TO SHARE WITH IN IF APPLICABLE
				if (privacy == "some") {
					$("#sharing_with_span a").each(function(){
						addFormField("sharing_with_IDs[]",$(this).attr("data-id"));
					});
				}

				//PUT TAGS IN
				$("#current_tags_span a").each(function(){
					addFormField("tag_IDs[]",$(this).attr("data-id"));
				});

				//SET ID
				addFormField("sid","<?php  echo $set_id; ?>");
			});
		});
	</script>

</head>

<body>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>

	<div id="content">

		<h2>Edit Set</h2>

		<div id="set_name_wrapper">
			<h3><?php echo $set_res[0]["name"]; ?></h3>
		</div>

		<p class="small">
			<a href="#" id="edit_set_name_link">Edit set name</a>
			<br />
			Created: <?php echo get_date($set_res[0]["created"]); ?>
			<br />
			Last edited: <?php echo get_date($set_res[0]["edited"]); ?>
			<br />
			<?php echo $set_res[0]["termcount"]; ?> terms
		</p>

		<p class="small">
			Tip: Double click to get a bigger box!
		</p>

		<!--MAIN SECTION: TYPE IN TERMS AND DEFS INTO TEXTBOXES-->
		<section>
			<p class="error" id="remove_error">You must have at least one term!</p>
			<div id="term_def_area">
			<?php
				foreach ($terms_res as $i) {
					echo("
						<div data-id='$i[id]'>
							<div><input type='text' value='$i[term]' /> :</div>
							<div><input type='text' value='$i[def]' /></div>
							<button title='Remove term'>X</button>
						</div>
					");
				}
			?>
			</div>

			<div><button id="add_term_button">Add term</button></div>
		</section>

		<hr />

		<button id="import_button">Import terms</button>

		<!--IMPORT TERMS AND DEFS BY COPY AND PASTE-->
		<section id="import_area">
			<p>
				Import terms:<br />
				(Paste into the box below)<br />
			</p>
			<textarea></textarea>
			<p><label for="import_separator_box">Separator: <input type="text" id="import_separator_box" /></label></p>
			<p><button>Add</button></p>
		</section>

		<hr />

		<button id="settings_button">Edit settings</button>

		<!--PRIVACY AND TAGS SECTION-->
		<section id="settings_area">

			<p>
				<label><input type="radio" name="privacy" value="none" />Don't share set</label>
				<label><input type="radio" name="privacy" value="some" />Share with some people</label>
				<label><input type="radio" name="privacy" value="all" />Share with everyone</label>
			</p>

			<div id="sharing_div">
				<label>Usernames of people to share with: <input type="text" /></label>
				<p id="user_search_results_area"></p>
				<p>Currently sharing with:
					<span id="sharing_with_span">
						<?php

							foreach ($privacy_res as $i) {
								echo "<a data-id='$i[userid]'>$i[username]</a>";
							}

						?>
					</span>
					(click to remove)
				</p>
			</div>

			<hr />

			<div id="tag_div">
				<p><label>Subject tags: <input type="text" /></label></p>
				<p id="tag_search_results_area"></p>
				<p>Current tags:
					<span id="current_tags_span">
					<?php

						foreach ($tags_res as $i) {
							echo "<a data-id='$i[tagid]'>$i[name]</a>";
						}

					?>
					</span>
					(Click to remove)
				</p>
			</div>

		</section>

		<hr />

		<form action="save.php" method="POST">
			<button>Save</button>
		</form>

		<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>

	</div>

	<textarea id="pop_up_textarea"></textarea>
</body>

</html>
