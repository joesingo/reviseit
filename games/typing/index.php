<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>

<div id="content">
	
	<h2>Typing game</h2>

	<div class="introduction">
		<p>Type the definition that corresponds to the term shown.</p>
		<p>Do it as quickly as you can with as few mistakes as possible to earn big points.</p>
		
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
	
	<div class="game">
		<h2 id="term"></h2>
		<p id="definition"></p>
		<p>Time remaining: <span id="timer"></span>s</p>
		<p id="lives"></p>
	</div>
	
	<div id="try_again_div">
		<p id="try_again_message"></p>
		<button>Try again</button>
	</div>
	
	<div id="end_game_div">
		<p>Your score was<br /><span class="statistic_span" id="final_score"></span></p>
		<p id="stats_p">
			Average time: <span class="statistic_span" id="average_time"></span><br />
			Average number of mistakes: <span class="statistic_span" id="average_mistakes"></span><br />
			Best: <span class="statistic_span" id="best_term"></span><br />
			Worst: <span class="statistic_span" id="worst_term"></span>
		</p>
		<button>Play again</button>
	</div>	
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>

</div>

<script type="text/javascript" src="/scripts/questions.js"></script>
<script type="text/javascript">

	function playGame() {

		var roundn = function(x,n) {
			//ROUND x TO n DECIMAL PLACES
			return Math.round(x * Math.pow(10,n)) / Math.pow(10,n);
		}		
		var start = function() {
			//RESET VARS FOR WHOLE GAME, HIDE INSTRUCTIONS, SHOW GAME AND ASK FIRST QUESTION
			
			questionsAsked = 0;
			scores = [];
			totalTimeTaken = 0;
			totalMistakes = 0;
			
			$(".introduction").hide();
			$("#end_game_div").hide();
			$(".game").show();
			
			askQuestion(0);
		}
		
		var askQuestion = function(n) {
			
			state = PLAYING;
			lettersDone = 0;
			mistakes = 0;
			startTime = Date.now();

			//PUT QUESTION IN
			$("#term").html(terms[n][0]);
			$("#definition").html("");
			
			//SHOW HEARTS
			$("#lives").html("");
			for (var i=0;i<gameSettings.maxMistakes;i++) {
				$("#lives").append("<img class='heart' src='/images/typing_game_heart.png' />");
			}

			//ROUND TIMER
			timeLeft = gameSettings.maxRoundTime;
			$("#timer").html(timeLeft);
			window.roundTimer = setInterval(function() {
				
				timeLeft -= 1
				
				if (timeLeft > 0) {
					$("#timer").html(timeLeft);
				}
				else {
					//OUT OF TIME
					tryAgain("You ran out of time!");
				}
			},1000);
			
		}
		
		var tryAgain = function(message) {
			
			state = TRY_AGAIN_SCREEN;
			window.clearInterval(window.roundTimer);
			$("#try_again_message").html(message);
			$(".game").hide();
			$("#try_again_div").show();
			
		}
		
		var continueGame = function() {
			$("#try_again_div").hide();
			$(".game").show();
			askQuestion(questionsAsked);
		}
		
		var checkLetter = function(s) {
			if (s.toLowerCase() == terms[questionsAsked][1].substr(lettersDone,1).toLowerCase()) {
				//GOT CORRECT LETTER
				lettersDone++;
				var charToAdd = terms[questionsAsked][1].substr(lettersDone - 1,1);
				charToAdd = charToAdd == " " ? "_" : charToAdd;
				$("#definition").append(charToAdd);
				
				var nextChar = terms[questionsAsked][1].substr(lettersDone,1);
				if (nextChar == " ") {
					checkLetter(" ");
				}
				
				if (lettersDone == terms[questionsAsked][1].length) {
					//FINISHED WORD
					
					window.clearInterval(window.roundTimer);
					questionsAsked++;
					
					//CALCULATE SCORE
					var newScore = (100 * timeLeft * (gameSettings.maxMistakes - mistakes)) / gameSettings.maxMistakes;
					newScore = newScore <= 0 ? 100 : newScore;
					scores.push(Math.round(newScore));
					
					totalTimeTaken += gameSettings.maxRoundTime - timeLeft;
					totalMistakes += mistakes;
					
					if (questionsAsked < terms.length) {
						//STILL QUESTIONS TO GO...
						askQuestion(questionsAsked);
					}
					else {
						//EVERYTHING HAS BEEN ASKED
						state = NOT_STARTED;
						
						var best = terms[0][0];
						var worst = terms[0][0];
						
						var lowest = scores[0];
						var highest = scores[0];
						
						var totalScore = 0;
						
						for (var i=0;i<scores.length;i++) {
							totalScore += scores[i];
							
							if (scores[i] > highest) {
								highest = scores[i];
								best = terms[i][0];
							}
							else if (scores[i] < lowest) {
								lowest = scores[i];
								worst = terms[i][0];
							}
						}
						
						$(".game").hide();
						
						$("#best_term").html(best);
						$("#worst_term").html(worst);
						
						$("#average_time").html(roundn(totalTimeTaken / terms.length,3) + "s");
						$("#average_mistakes").html(roundn(totalMistakes / terms.length,3));
						
						$("#final_score").html(roundn(totalScore,3));
						
						$("#end_game_div").show();

						saveScores(totalScore);
					}
				} 
			}
			else {
				//GOT IT WRONG
				mistakes++;
				
				//REMOVE A HEART :'(
				$("img.heart:last-child").remove();
				
				if (mistakes == gameSettings.maxMistakes) {
					//TOO MANY MISTAKES
					tryAgain("You made too many mistakes!");
				}
			}
		}
		
		$(document).keypress(function(e) {
			
			if (state == PLAYING && e.which != 13 && e.which != 32) { //DO NOT CHECK IF SPACE OR ENTER IS PRESSED

				checkLetter(String.fromCharCode(e.which));
			}
			else if (state == TRY_AGAIN_SCREEN) {
				if (e.which == 32) continueGame();
			}
						
		});
		
		var allTerms = decode(cipheredTerms);

		var terms = [];
		
		const gameSettings = {
			"maxRoundTime": 30,
			"maxMistakes": 10,
			"correctMessageLength": 3
		};
				
		//STATE CONSTANTS
		const NOT_STARTED = 1;
		const PLAYING = 2;
		const TRY_AGAIN_SCREEN = 3;
		
		var state;
		var scores;
		var questionsAsked;
		var startTime;
		var lettersDone;
		var timeLeft;
		var totalTimeTaken;
		var totalMistakes;
		
		state = NOT_STARTED;
		
		/* - - - - */
		
		$(".game, #try_again_div, #end_game_div").hide();
		$(".introduction button").click(function(){

			//GET QUESTIONS WHEN USER HAS CHOSEN QUESTION TYPE
			var quizData = getMultipleChoiceQuestions({
				randomOrder: true,
				options: 1,
				questionType: $("#question_type_dropdown").val(),
				terms: allTerms
			});
			
			$.each(quizData.questions,function(i){
				terms.push( [quizData.questions[i], quizData.answers[i]] );
			});
			
			start();
		});
		$("#end_game_div button").click(start);
		$("#try_again_div button").click(continueGame);

	}

	$("head").append("<link rel='stylesheet' href='/games/typing/style.css' />");
	playGame();
	
</script>