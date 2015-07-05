<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>

<div id="content">
	
	<h2>Multiple choice quiz</h2>
	
	<div id="introduction">
		<p>
			<label for="question_type_dropdown">Question type:</label>
			<select id="question_type_dropdown">
				<option value="term">Show me term and ask for definition</option>
				<option value="def">Show me definition and ask for term</option>
				<option value="mix">Mix it up</option>
			</select>
		</p>
		<button>Start</button>
	</div>
	
	<div id="quiz_area"><ol></ol></div>
	
	<div id="not_finished_div" class="bold">Please answer all the questions!</div>
	
	<div id="results_div">
		<p>
			You scored <b><span id="score_span"></span></b>/<span id="max_score_span"></span>
			(<span id="perc_score_span"></span>%)
		</p>
	</div>

	<div id="saving_scores_div"></div>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
	
</div>

<script type="text/javascript" src="/scripts/questions.js"></script>
<script type="text/javascript">
	function playGame() {

		var allTerms = decode(cipheredTerms);
	
		var addQuestion = function(question,options,index,termID) {
			var s = "<li>";
			s += "<div>";
			s += "<p>" + question + "</p>";
			s += "<p>";
			$.each(options,function(i){
				s += "<label><input type='radio' name='question" + index + "' value='" + i + "' />" + options[i] + "</label><br />";
			});
			s += "</p>";
			s += "</div";
			s += "</li>";
			
			$("#quiz_area ol").append(s);
		}

		$("#content").css("text-align","left");
		$("#quiz_area ol").css("padding","0");
		$("#results_div, #saving_scores_div").css("font-size","1.2em");

		$("#not_finished_div, #results_div").hide();
		var quizData;

		$("#introduction button").click(function(){
		
			//GET QUIZ DATA ONCE USER HAS DECIDED QUESTION TYPE
			quizData = getMultipleChoiceQuestions({
				terms: allTerms,
				questionType: $("#question_type_dropdown").val(),
				randomOrder: true,
				options: 4
			});
			
			//ADD QUESTIONS TO PAGE
			$.each(quizData.questions,function(i){
				addQuestion(quizData.questions[i], quizData.options[i], i, allTerms[i][2]);
			});
			$("#quiz_area").append("<p><button>Submit</button></p>");

			$("#quiz_area li").css("list-style","none");
			$("#quiz_area li > div p:first-child").css("font-weight","bold");
			$("#quiz_area li > div p:last-child").css("line-height","1.5em");

			$("#introduction").hide();
			
			//HANDLE SUBMIT BUTTON CLICK
			$("#quiz_area button").click(function(){
							
				if ( $("#quiz_area  li input:checked").length != quizData.questions.length ) {
					$("#not_finished_div").show();
					return false;
				}
				
				//REMOVE SUBMIT BUTTON TO STOP THE USER BEING CHEEEKY AND RE-SUBMITTING AFTER BEING TOLD ANSWERS
				$(this).remove();
				
				$("#not_finished_div").hide();
				var score = 0;
				
				$("#quiz_area li").each(function(i){

					var checkedBox = $(this).find("input:checked");
					var correctAnswerIndex = $.inArray(quizData.answers[i], quizData.options[i]);
					
					if ($(checkedBox).val() == correctAnswerIndex) {
						score++;
					}
					else {						
						//MARK THEIR ANSWER AS WRONG
						$(checkedBox).parent().addClass("bold incorrect");
					}
					
					//SHOW CORRECT ANSWER WHETHER THEY GOT IT RIGHT OR NOT
					$(this).find("input[value=" + correctAnswerIndex + "]").parent().addClass("bold correct");
				});

				$(".correct").css("color","green");
				$(".incorrect").css("color","red");
				
				$("#score_span").html(score);
				$("#max_score_span").html(quizData.questions.length);
				
				var perc = Math.floor(100 * 100 * score / quizData.questions.length) / 100;
				$("#perc_score_span").html(perc);
				if (perc == 100) {
					$("#results_div").append("<p>Well done!</p>");
				}
				
				$("#results_div").show();

				saveScores(score,function() {
					$("#saving_scores_div").html("Saving scores...");
				},function(res) {
					$("#saving_scores_div").html(res);
				})
			});
		});
	
	}

	playGame();

</script>
