<table id="game">
	<tr>
		<td data-option="0"></td>
		<td data-option="1"></td>
	</tr>
	<tr>
		<td data-option="2"></td>
		<td data-option="3"></td>
	</tr>
</table>

<div id="question_wrapper">
	<div id="question"></div>
</div>

<div id="message_div">
	<span></span>
</div>

<script type="text/javascript" src="/scripts/questions.js"></script>
<script type="text/javascript">

	function playGame() {

		//CONSTANTS
		const ANIM_TIME = 0.5;
		const WAIT_TIME = 0.3;

		const SCORE_DECREASE_PS = 15; //AMOUNT SCORE DECREASES BY EACH SECOND
		const STARTING_SCORE = 100;
		const ADDED_SCORE = 75; //AMOUNT THAT IS ADDED TO SCORE FOR A CORRECT ANSWER
		const INCORRECT_PENALTY = 25;

		const SCORE_BAR_SCALE = 0.7; //PIXELS PER SCORE POINT FOR SCORE BAR
		const SCORE_BAR_HEIGHT = 25;
		const SCORE_BAR_COLOUR = "#9B30FF";
		const SCORE_BAR_BORDER_COLOUR = "white";

		const SQUARE_SIZE = 80;

		const C_SPIN_FREQ = 10;

		/* - - - - - */

		var canvas = document.createElement("canvas");
		canvas.width = window.innerWidth;
		canvas.height = window.innerHeight;
		var c = canvas.getContext("2d");

		var scoreCanvas = document.createElement("canvas");
		scoreCanvas.width = window.innerWidth;
		scoreCanvas.height = SCORE_BAR_HEIGHT;
		var sc = scoreCanvas.getContext("2d");

		$(scoreCanvas).css({
			position: "fixed",
			left: 0,
			top: "100%",
			marginTop: "-" + (SCORE_BAR_HEIGHT + 20) + "px"
		});

		/* - - - - - */

		//GAME VARS
		var quizData;
		var questionsAnswered;
		var score;
		var colours = ["#D2315D","#22B5BF","#E98813","#88C134"];

		/* - - - - - */

		var getQuadCoords = function(quad) {
			return {
				x: quad % 2 == 0 ? 0 : 1,
				y: quad < 2 ? 0 : 1
			};
		}

		var getQuadNumber = function(x,y) {
			return 2*y + x;
		}

		/*var getRgbFromHex = function(c) {
			//RETURN RGB VALUES FROM HEX COLOUR (WITH LEADING #)
			return {
				r: parseInt(tc.substr(1,2),16),
				g: parseInt(tc.substr(3,2),16),
				b: parseInt(tc.substr(5,2),16)
			};
		}*/

		var drawQuadrants = function(params) {
			//DRAW THE 4 QUADRANTS
			//PARAMS:
			//startX - X-COORD IN MAIN CANVAS OF QUADS
			//startY - Y-COORD
			//width - TOTAL WIDTH OF QUADS
			//height - TOTAL HEIGT
			//focusQuad - THE QUADRANT THAT IS A DIFFERENT SIZE TO THE OTHERS
			//			  (USED FOR THE ANIMATION)
			//			  QUADS ARE LAID OUT LIKE SO:
			//				0  1
			//				2  3
			//size - NUMBER BETWEEN 0 AND 1 FOR HOW BIG FOCUSED QUAD SHOULD BE (AS A PERCENTAGE OF TOTAL WIDTH/HEIGHT)

			if (!params) {
				var params = {
					startX: 0,
					startY: 0,
					width: canvas.width,
					height: canvas.height,
					focusQuad: 0,
					size: 0.5
				};
			}

			var focusQuadCoords = getQuadCoords(params.focusQuad);

			var cy = 0;
			for (var y=0;y<2;y++) {

				var cx = 0;
				var height = params.height * (y == focusQuadCoords.y ? params.size : 1 - params.size);

				for (var x=0;x<2;x++) {
					var width = params.width * (x == focusQuadCoords.x ? params.size : 1 - params.size);

					c.fillStyle = colours[2*y+x];
					c.fillRect(
						cx + params.startX,
						cy + params.startY,
						width,
						height
					);
					cx += width;
				}
				cy += height;
			}
		}

		var expand = function(quad,callback){
			//EXPAND QUADRANT TO TAKE UP WHOLE CANVAS

			var size = 0.5;

			//EXPAND QUADRANT
			var animationInterval = setInterval(function(){
				
				//IF NOT FINISHED EXPANDING...
				if (size < 1) {

					//INCREASE SIZE AND REDRAW QUADRANTS
					size +=  1 / (200*ANIM_TIME);
					drawQuadrants({
						startX: 0,
						startY: 0,
						width: canvas.width,
						height: canvas.height,
						focusQuad: quad,
						size: size
					});

					var coords = getQuadCoords(quad);

					//DRAW INNER QUADRANTS (FOR GROOVY EFFECT)
					/*drawQuadrants({
						startX: coords.x == 0 ? 0 : canvas.width * (1-size),
						startY: coords.y == 0 ? 0 : canvas.height * (1-size),
						width: size * canvas.width,
						height: size * canvas.height,
						focusQuad: 0,
						size: 0.5
					});*/
				}

				//IF FINISHED EXPANDING...
				else {

					callback();

					//STOP INTERVAL
					clearInterval(animationInterval);
				}
			},10);
		}

		var squares = function(quad,callback) {
			//RANDOMLY FILL SCREEN WITH LITTLE SQUARES

			c.fillStyle = colours[quad];

			//SIZE OF GRID OF SQUARES
			var gridWidth = Math.ceil(canvas.width/SQUARE_SIZE);
			var gridHeight = Math.ceil(canvas.height/SQUARE_SIZE);

			//ARRAY CONTAINING CANVAS COORDS OF EACH CELL
			var cellCoords = [];
			for (var i=0;i<gridWidth;i++) {
				for (var j=0;j<gridHeight;j++) {
					cellCoords.push([i*SQUARE_SIZE,j*SQUARE_SIZE]);
				}
			}

			//SHUFFLE ARRAY SO SQUARES ARE FILLED IN RANDOMLY
			shuffleArray(cellCoords,SQUARE_SIZE*SQUARE_SIZE);

			var count = 0;

			var animationInterval = setInterval(function(){
				//GO THROUGH CELL COORDS ARRAY AND FILL IN EVERY SQUARE!

				c.fillRect(
					cellCoords[count][0], cellCoords[count][1], SQUARE_SIZE, SQUARE_SIZE
				);
				count++;

				//ALL CELLS HAVE BEEN FILLED IN...
				if (count == cellCoords.length) {
					
					callback();

					clearInterval(animationInterval);
				}
			},1000*ANIM_TIME/(gridWidth*gridHeight));
		}

		var colourSpin = function(quad,callback) {

			var quadColour = colours[quad];
			var endTime = Date.now() + ANIM_TIME*1000;

			var animationInterval = setInterval(function(){

				var temp = colours.slice();
				colours = [
					temp[2], temp[0], temp[3], temp[1]
				];

				drawQuadrants();

				if (Date.now() >= endTime) {
					c.fillStyle = quadColour;
					c.fillRect(0,0,canvas.width,canvas.height);

					callback();

					clearInterval(animationInterval);
				}

			},1000 / C_SPIN_FREQ);
		}

		var animations = [expand,squares,colourSpin];

		var drawScoreBar = function(s) {
			sc.clearRect(0,0,scoreCanvas.width,scoreCanvas.height);

			var width = SCORE_BAR_SCALE * s;
			var x = 0.5*scoreCanvas.width - 0.5*width;
			var bw = 2;

			sc.fillStyle = SCORE_BAR_BORDER_COLOUR;
			sc.fillRect(x - bw, 0, width + 2*bw, SCORE_BAR_HEIGHT);

			sc.fillStyle = SCORE_BAR_COLOUR;
			sc.fillRect(x, bw, width, SCORE_BAR_HEIGHT - 2*bw);
		}

		var startGame = function() {

			quizData = getMultipleChoiceQuestions({
				terms: decode(cipheredTerms),
				options: 4,
				questionType: "mix",
				randomOrder: true
			});

			questionsAnswered = 0;
			score = STARTING_SCORE;

			//CLICKING AN OPTION...
			$("#game td").off().click(function(){

				//IF CORRECT ANSWER...
				if ( $(this).html() == quizData.answers[questionsAnswered] ) {
					questionsAnswered++;

					var n = $(this).data("option");
					
					endRound(n);
				}
				else {
					score -= INCORRECT_PENALTY;
					drawScoreBar(score);
				}
			});

			$("#message_div").css("display","table");
			$("#message_div").hide();
			$("#message_div").html("Correct");

			startRound();
		}

		var endGame = function() {

			saveScores(score,function(){

				$("#question").html("Game finished");
				drawQuadrants();

				$("#message_div").html("Your score was " + score + "<br />Click to play again");
				$("#message_div").show();

				$("body").click(function(){
					$(this).off();
					startGame();
				});
				
			});

		}

		var startRound = function() {

			if ( questionsAnswered == quizData.questions.length) {
				endGame();
				return;
			}

			//PUT QUESTION IN
			$("#question").html( quizData.questions[questionsAnswered] );

			//PUT OPTIONS IN
			for (var i=0;i<4;i++) {
				$("#game td[data-option=" + i + "]").html( quizData.options[questionsAnswered][i] );
			}

			$("#game").show();

			drawQuadrants();

			//MAKE SCORE SLOWLY TRICKLE AWAY
			window.scoreTimer = setInterval(function(){
				if (score > 0) {
					score -= SCORE_DECREASE_PS / 10
					drawScoreBar(score);
				}
				else if (score < 0) {
					score = 0;
					drawScoreBar(score);
				}
			},100);
		}

		var endRound = function(quad) {
			$("#game").fadeOut();
			$("#question_wrapper").fadeOut();
			$("#message_div").fadeIn();

			//CHOOSE RANDOM ANIMATION
			var chosen = animations[Math.floor(Math.random()*animations.length)];

			//DO ANIMATION
			chosen(quad,function(){
				
				//WAIT WAIT_TIME SECONDS WHEN ANIMATION FINISHES
				setTimeout(function(){

					shuffleArray(colours,5);
					$("#message_div").hide();
					$("#question_wrapper").show();

					startRound();

				},1000*WAIT_TIME);

			});

			clearInterval(window.scoreTimer);

			//INCREASE SCORE (AS ANIMATION RUNS AND DURING WAIT)
			var endTime = Date.now() + ANIM_TIME*1000 + WAIT_TIME*1000;
			var endScore = score + ADDED_SCORE;
			var scoreInterval = setInterval(function(){
				
				score += 0.1*ADDED_SCORE / (ANIM_TIME + WAIT_TIME)
				drawScoreBar(score);

				if (Date.now() >= endTime || score > endScore) {
					score = endScore;
					drawScoreBar(score);

					clearInterval(scoreInterval);
				}
			},100);
		}

		//ADD CANVASES TO PAGE
		$("body").append(canvas);
		$("body").append(scoreCanvas);

		drawQuadrants();
		$("#question").html("Quadrant");
		$("#message_div").html("Click the correct answer as quickly as you can!<br />Click to start");
		$("body").click(function(){
			$(this).off();
			startGame();
		});

		window.addEventListener("resize",function(){
			canvas.width = window.innerWidth;
			canvas.height = window.innerHeight;
			drawQuadrants();
		});

	}

	$("head").append("<link rel='stylesheet' href='/games/quadrant/style.css' />");

	playGame();

</script>