<div id="question"></div>
<ol id="optionslist">
	<li id="option0"></li>
	<li id="option1"></li>
	<li id="option2"></li>
	<li id="option3"></li>
</ol>

<div id="canvasdiv"></div>
<div id="round_time"></div>
<div id="continuediv" style="display:none;">
	<div>
		<h2 id="continuemessage"></h2>
		<p>
			The correct answer was <span id="correctanswer"></span>
		</p>
	</div>
	<p>Press space to continue</p>
</div>

<div id="endgamediv" style="display:none;">
	<h2>Game finished</h2>
	<p>Your score was <span id="finalscore"></span></p>
	<p id="saving_score_p" style="display:none;">Saving score...</p>
	<p id="play_again_p" style="display:none;">Press space to play again</p>
</div>

<script type="text/javascript" src="/scripts/questions.js"></script>
<script type="text/javascript" src="/scripts/game.js"></script>
<script type="text/javascript">
	
	function playGame() {

		function SnakePiece(x,y,dir,turns,colour) {
			this.x = x;
			this.y = y;
			this.dir = dir;
			this.turns = turns;
			this.colour = colour;
			
			this.move = function() {
				
				for (var i=0;i<this.turns.length;i++) {
				
					//TURN IF IT IS TIME
					if (this.turns[i].waitTime == 0) {
						this.dir = this.turns[i].dir;
						
						this.turns.splice(i,1);
						i--;
					}
					else this.turns[i].waitTime--;
				}
				
				//DO THE ACTUAL MOVING
				switch (this.dir) {
					case 1:
						this.y--;
						break;
					case 2:
						this.x++;
						break;
					case 3:
						this.y++;
						break;
					case 4:
						this.x--;
						break;
				}
			}
			
			this.turn = function(dir,time) {
				this.turns.push(
					{
						"waitTime": time,
						"dir": dir
					}
				);
			}
		}

		function Snake(x,y,length) {
			this.pieces = [];
			this.previousTailCoords = [];
			
			this.addPiece = function(x,y,dir,turns) {
					this.pieces.push(new SnakePiece(x,y,dir,turns,gameSettings.snakeColours[this.pieces.length % gameSettings.snakeColours.length]));
			}
			
			//MAKE STARTING SNAKE (DIRECTION IS 2)
			for (var i=0;i<length;i++) {
				this.addPiece(x-i,y,2,[]);
			}
			
			this.move = function() {
				
				this.previousTailCoords = [this.pieces[this.pieces.length - 1].x,this.pieces[this.pieces.length - 1].y];
				for (var i=0;i<this.pieces.length;i++) {
					this.pieces[i].move();
				}
				
				//CHECK FOR HITTING WALLS
				if (this.pieces[0].x == -1 || this.pieces[0].x == gameSettings.width || this.pieces[0].y == -1 || this.pieces[0].y == gameSettings.height) {
					snakeDie(gameSettings.hitTheWallMessage);
				}
				
				//HAVEN'T HIT WALLS, CHECK OTHERS
				else if (arena[this.pieces[0].x][this.pieces[0].y] != 0) {
					
					//CHECK FOR COLLISION WITH SELF
					for (var i=1;i<this.pieces.length;i++) {
						if (this.pieces[i].x == this.pieces[0].x && this.pieces[i].y == this.pieces[0].y) {

							//HIT SELF
							snakeDie(gameSettings.hitSelfMessage);
							break;
						}
					}
					
					//CHECK FOR COLLISION WITH LETTERS
					if (arena[this.pieces[0].x][this.pieces[0].y].substr(8) == "correct") {
						//CORRECT ANSWER...

						calculateScore();

						if (window.roundTimer) {
							//STOP ROUND TIMER
							window.clearInterval(window.roundTimer);
						}
					
						if (questions.length == 0) {
							endGame();
						}
						else {
							MPS += gameSettings.MPSIncrease;
							this.grow(gameSettings.growAmount);
							startRound();
						}
					}
					else if (arena[this.pieces[0].x][this.pieces[0].y].substr(8) == "incorrect") {
						//INCORRECT ANSWER...

						snakeDie(gameSettings.incorrectAnswerMessage);
					}
				}
			}
			
			this.turn = function(dir) {
				//CHECK THAT CHEEKY USER IS NOT TRYING TO TURN BACK ON THEMSELVES
				var bannedDirection = (dir + 2) % 4 != 0 ? (dir + 2) % 4 : ((dir + 2) % 4) + 4;
				
				if (this.pieces[0].dir != bannedDirection) {
					for (var i=0;i<this.pieces.length;i++) {
						this.pieces[i].turn(dir,i);
					}
				}
			}
			
			this.grow = function(n) {
				for (var i=0;i<n;i++) {
					//COPY THE LAST PIECE (CHANGING X AND Y TO PUT NEW PIECE ON THE END) AND INCREASE WAIT TIME ON EACH TURN BY 1
					
					var lastPiece = this.pieces.slice(-1)[0];
					var x = lastPiece.x;
					var y = lastPiece.y;
					
					switch (lastPiece.dir) {
						case 1:
							y++;
							break;
						case 2:
							x--;
							break;
						case 3:
							y--;
							break;
						case 4:
							x++;
							break;
					}
						
					var turns = [];
					for (var j=0;j<lastPiece.turns.length;j++) {
						turns[j] = {};
						turns[j].dir = lastPiece.turns[j].dir;
						turns[j].waitTime = lastPiece.turns[j].waitTime + 1;
					}
					
					this.addPiece(x,y,lastPiece.dir,turns)
				}
			}
		}
		
		var updateArena = function() {
			if (state == PLAYING) { //PROBABLY RUBBISH HACK BECAUSE THIS SHOULD NEVER BE CALLED WHEN NOT PLAYING ANYWAY...
				//THE ONLY PART OF THE ARENA THAT NEEDS TO BE CLEARED IS WHERE THE TAIL WAS LAST TIME...
				arena[spencer.previousTailCoords[0]][spencer.previousTailCoords[1]] = 0;
				
				//FILL WITH SNAKE
				for (var i=0;i<spencer.pieces.length;i++) {
					var x = spencer.pieces[i].x;
					var y = spencer.pieces[i].y;
					
					//CHECK THAT PIECE IS ACTUALLY ON BOARD BEFORE TRYING TO PUT IT IN
					if (x >= 0 && y >= 0 && x < gameSettings.width && y < gameSettings.height) {
						arena[x][y] = spencer.pieces[i].colour;
					}
				}
			}
		}
		
		var setupGame = function() {
			resetSnake();
			MPS = gameSettings.startMPS;
			timer = 1/MPS;
			state = NOT_STARTED;
			
			var quizData = getMultipleChoiceQuestions({
				terms: allTerms,
				randomOrder: true,
				questionType: "term",
				options: 4
			});
			
			questions = quizData.questions;
			answers = quizData.answers;
			options = quizData.options;
			IDs = quizData.IDs;

			score = 0;
		}
		
		var resetSnake = function() {
			spencer = new Snake(20,20,gameSettings.startingSnakeLength);
		}
		
		var startRound = function() {
			//GETS RID OF OLD BLOBS, PUTS NEW ONES IN AND SHOWS QUESTION/OPTIONS
			
			//DO ROUND TIMER
			roundTime = 0;
			window.roundTimer = window.setInterval(function(){
				if (state == PLAYING) {
					roundTime += 0.1;
					
					//ROUND TIME TO 1 DP AND ADD '.0' IF IT'S A WHOLE NUMBER
					timeString = Math.floor(roundTime * 10) / 10;
					if (Math.floor(timeString) == timeString) timeString = timeString + ".0";
					
					document.getElementById("round_time").innerHTML = timeString;
				}
			},100);
			
			myGame.clear();
						
			//CLEAR ARENA
			for (var i=0;i<gameSettings.width;i++) {
				for (var j=0;j<gameSettings.height;j++) {
					arena[i][j] = 0;
				}
			}
			
			correctAnswer = answers[0];
			currentID = IDs[0];
			
			//PUT BLOBS IN
			var blobs = [];
			for (var i=0;i<4;i++) {
				var x, y;
				while (true) {
					x = Math.floor(Math.random() * gameSettings.width);
					y = Math.floor(Math.random() * gameSettings.height);
					
					var okaySpace = arena[x][y] == 0;
					
					if (okaySpace) {
						//CHECK THAT NEW SPACE FOR BLOB IS NOT TOO CLOSE TO OTHER BLOBS OR HEAD OF SNAKE
						
						for (var j=0;j<blobs.length;j++) {
							if (Math.abs(blobs[j][0] - x) < 3 || Math.abs(blobs[j][1] - y) < 3) {
								okaySpace = false;
								break;
							}
						}
						
						if (Math.abs(spencer.pieces[0].x - x) < 3 || Math.abs(spencer.pieces[0].y - y) < 3) {
							okaySpace = false;
						}
					}
					
					if (okaySpace) {
						blobs.push([x,y]);
						break;
					}
				}
				arena[x][y] = gameSettings.blobColours[i] + (options[0][i] == correctAnswer ? ";correct" : ";incorrect");
				
				//SAVE CORRECT ANSWER COLOUR TO MAKE CONTINUE BOX NICE
				if (options[0][i] == correctAnswer) correctAnswerColour = gameSettings.blobColours[i];
			}
			
			//SHOW QUESTION AND OPTIONS
			document.getElementById("question").innerHTML = questions[0];
			for (var i=0;i<options[0].length;i++) {
				document.getElementById("option" + i).innerHTML = options[0][i];
			}
			
			//REMOVE TOP QUESTION, ANSWER, OPTIONS AND TERMID
			questions.splice(0,1);
			answers.splice(0,1);
			options.splice(0,1);
			IDs.splice(0,1);
			
			//MAKE BACKGROUNDS COLOURED
			for (var i=0;i<4;i++) {
				document.getElementById("option" + i).style.background = gameSettings.blobColours[i];
			}
			
			state = PLAYING;
		}
		
		var endGame = function() {

			document.getElementById("endgamediv").style.background = correctAnswerColour;
			document.getElementById("finalscore").innerHTML = score;
			document.getElementById("endgamediv").style.display = "block";

			state = SAVING_SCORE;

			saveScores(score,function(){
				document.getElementById("saving_score_p").style.display = "block";
			},function(){
				document.getElementById("saving_score_p").style.display = "none";
				document.getElementById("play_again_p").style.display = "block";
				//state = NOT_STARTED;
			});

			setupGame();
		}
		
		var shuffleArray = function(a,n) {
			var r1, r2, temp;
			for (var i=0;i<n;i++) {
				r1 = Math.floor(Math.random() * a.length);
				r2 = (r1 + Math.floor(Math.random() * (a.length - 1)) + 1) % a.length;
				
				temp = a[r1];
				a[r1] = a[r2];
				a[r2] = temp;
			}
		}
		
		var snakeDie = function(message) {
			
			//STOP ROUND TIMER
			if (window.roundTimer) window.clearInterval(window.roundTimer);
			
			//NEED TO DRAW SNAKE IN DEAD POSITION BEFORE FLASHING - OTHERWISE THE TAIL PIECE WILL NOT FLASH
			updateArena();
		
			flashSnake();
							
			//SHOW THEM RIGHT ANSWER
			document.getElementById("correctanswer").innerHTML = correctAnswer;
			
			document.getElementById("continuemessage").innerHTML = message;
			document.getElementById("continuediv").style.background = correctAnswerColour;
			document.getElementById("continuediv").style.display = "block";
		}
		
		var flashSnake = function() {
			//STARTS THE FLASHING BUT WILL FLASH INDEFINITELY
			state = FLASHING;
			
			var waitTime = 1000 / gameSettings.flashFrequency; //TIME BETWEEN EACH FLASH IN MS
			var flashCount = 0;
			var timerStuff = function() {
				for (var i=0;i<spencer.pieces.length;i++) {
					var x = spencer.pieces[i].x;
					var y = spencer.pieces[i].y;
					
					if (x >= 0 && y >= 0 && x < gameSettings.width && y < gameSettings.width) {
						arena[x][y] = (flashCount % 2 == 0 ? spencer.pieces[i].colour : 0);
					}
				}
				arenaFrame.draw();
				arenaFrame.display();
				
				flashCount++;
			}
			
			timerStuff();
			window.flashTimer = setInterval(timerStuff,waitTime);			
		}
		
		var showMainMenu = function() {
			myGame.c.fillStyle = gameSettings.backgroundColour;
			myGame.c.fillRect(0,0,myGame.canvas.width,myGame.canvas.height);
			
			myGame.c.fillStyle = gameSettings.fontColour;
			myGame.c.textBaseline = "middle";
			myGame.c.textAlign = "center";
			
			myGame.c.font = "60px Courier New";
			myGame.c.fillText("SNAKE",myGame.canvas.width / 2,100);
			
			myGame.c.font = "30px Courier New";
			myGame.c.fillText("WASD to move",myGame.canvas.width / 2,200);
			myGame.c.fillText("Eat the correct letter!",myGame.canvas.width / 2,280);
			myGame.c.fillText("Press space to start",myGame.canvas.width / 2,360);
		}
		
		var calculateScore = function() {
			thisScore = 60 - Math.floor(roundTime);
			if (thisScore < 0) thisScore = 0;
						
			score += thisScore
		}
		
		var togglePause = function() {
			if (state == PAUSED) {
				myGame.clear();
				state = PLAYING;
				document.getElementById("question").style.visibility = "visible";
				document.getElementById("optionslist").style.visibility = "visible";
			}
			else if (state == PLAYING) {
				state = PAUSED;
				
				document.getElementById("question").style.visibility = "hidden";
				document.getElementById("optionslist").style.visibility = "hidden";
				
				myGame.c.fillStyle = gameSettings.backgroundColour;
				myGame.c.fillRect(0,0,myGame.canvas.width,myGame.canvas.height);
				
				myGame.c.fillStyle = gameSettings.fontColour;
				myGame.c.textBaseline = "middle";
				myGame.c.textAlign = "center";
				
				myGame.c.font = "60px Courier New";
				myGame.c.fillText("PAUSED",myGame.canvas.width / 2,myGame.canvas.height / 2);
			}
		}

		//-------------------------------------------------------------------
		
		const gameSettings = {
			"width": 50,
			"height": 50,

			"canvasWidth": 500,
			"canvasHeight": 500,
			
			"startMPS": 10,
			"MPSIncrease": 1, //MOVES PER SECOND INCREASE PER ROUND
			"nextRoundWait": 2,
			
			"startingSnakeLength": 8,
			"growAmount": 3, //PIECES TO GROW BY FOR EVERY CORRECT ANSWER
			
			"flashFrequency": 5, //FREQUENCY IN HZ OF SNAKE FLASHES
						
			"backgroundColour": "#000000",
			"foregroundColour": "#ffffff",
			"fontColour": "white",
			"snakeColours": ["#D2315D","#22B5BF","#E98813","#88C134"],
			"blobColours": ["#D2315D","#22B5BF","#E98813","#88C134"],
			
			"hitTheWallMessage": "Try not to hit the walls",
			"hitSelfMessage": "Don't hit yourself",
			"incorrectAnswerMessage": "Incorrect",
		};

		var myGame = new Game(gameSettings.canvasWidth,gameSettings.canvasHeight);

		var allTerms = decode(cipheredTerms);
				
		//STATE CONSTANTS
		const NOT_STARTED = 0;
		const PLAYING = 1;
		const PAUSED = 2;
		const FLASHING = 3;
		const SAVING_SCORE = 4;
				
		//SET UP ARENA ARRAY
		var arena = [];
		for (var i=0;i<gameSettings.width;i++) {
			arena.push([]);
			for (var j=0;j<gameSettings.height;j++) {
				arena[i].push(0);
			}
		}
		
		var arenaFrame = new Frame(myGame,0,0,gameSettings.canvasWidth,gameSettings.canvasHeight);
		arenaFrame.draw = function() {	

			//DRAW BACKGROUND
			this.c.fillStyle = gameSettings.backgroundColour;
			this.c.fillRect(0,0,this.canvas.width,this.canvas.height);
			
			//DRAW FOREGROUND THINGS
			this.c.fillStyle = gameSettings.foregroundColour;
			
			for (var i=0;i<gameSettings.width;i++) {
				for (var j=0;j<gameSettings.height;j++) {
					
					//IF NOT BLANK...
					if (arena[i][j] != 0) {
						this.c.fillStyle = arena[i][j].substr(0,7)
						
						//FILL IT IN!
						this.c.fillRect(
							i * (this.canvas.width / gameSettings.width),
							j * (this.canvas.height / gameSettings.height),
							this.canvas.width / gameSettings.width,
							this.canvas.height / gameSettings.height
						);
					}
				}
			}
		}

		var state;
		var spencer;
		var MPS;
		var timer;
		var gameStarted;
		var roundTime;
		
		var questions;
		var answers;
		var options;
		var IDs = [];

		var currentID;

		var correctAnswer;
		var correctAnswerColour;
		
		var score;
				
		//SET UP CONTROLS FOR MOVING SNAKE
		myGame.setControl("w","moveUp",function(){spencer.turn(1);});
		myGame.setControl("a","moveLeft",function(){spencer.turn(4);});
		myGame.setControl("d","moveRight",function(){spencer.turn(2);});
		myGame.setControl("s","moveDown",function(){spencer.turn(3);});
        //myGame.setControl("g","growSnake",function(){spencer.grow();});
		
		//OTHER CONTROLS
		myGame.setControl("space","coolnamehere",function() {
			
			//START GAME IF NOT STARTED
			if (state == NOT_STARTED) {
			
				//HIDE END GAME DIV (INCASE THEY ARE PLAYING AGAIN)
				document.getElementById("endgamediv").style.display = "none";
				document.getElementById("play_again_p").style.display = "none";
			
				state = PLAYING;
				startRound();
			}
			//CONTINUE GAME IF WAITING
			else if (state == FLASHING) {
				
				//STOP FLASHING (IF FLASHING)
				window.clearInterval(window.flashTimer);
				
				//RESET CONTINUE POP UP
				document.getElementById("continuediv").style.display = "none";
				document.getElementById("continuemessage").innerHTML = "";
				document.getElementById("correctanswer").innerHTML = "";
				
				if (questions.length == 0) {
					//IF ALL QUESTIONS HAVE BEEN ASKED DO END GAME
					endGame();
				}
				else {
					//STILL QUESTIONS TO GO, SO CARRY ON
					resetSnake();
					startRound();
					state = PLAYING;
				}
			}
			else if (state == SAVING_SCORE) {
				alert("hoo");
			}
		});
		myGame.setControl("p","pauseButton",togglePause);
		myGame.setControl("esc","pauseButton2",togglePause);
		myGame.setControl("n","debugButton",flashSnake);
		
		//MAIN LOOP
		myGame.update = function(dt) {
			if (state == PLAYING) {
				timer += dt;
				if (timer >= 1/MPS) {
					timer -= 1/MPS;
					
					spencer.move(); //MOVE SNAKE
					updateArena(); //UPDATE SQUARES
					arenaFrame.draw();
					arenaFrame.display(); //DRAW ARENA
				}
			}
		};
		
		setupGame(); //INIT VARS
		showMainMenu();

		myGame.run(document.getElementById("canvasdiv"));
	}

	$("head").append("<link rel='stylesheet' href='/games/snake/style.css' />");
	playGame();

</script>