<div id="intro">
	<h1>Shape Shooter</h1>
	<p>Shoot the shapes as they move down the screen,
	but don't shoot the'special shape' (shown at the start of each round)!
	</p>
	<p>If you let a normal shape hit the bottom or shoot the special shape
	(or 1 minute passes) you will have to answer a question
	</p>
	<p>Move with arrow keys and shoot with space</p>
	<p>Press space to start!</p>
</div>
<div id="question_div">
	<p id="message"></p>
	<p id="question"></p>
	<textarea></textarea>
	<p id="result"></p>
	<p id="continue">Press space to continue</p>
</div>
<div id="end">
	<h1>Game Over</h1>
	<p id="end_result"></p>
	<p>Press space to play again</p>
</div>

<script type="text/javascript" src="/scripts/game.js"></script>
<script type="text/javascript" src="/scripts/questions.js"></script>
<script type="text/javascript">
/*SHAPE SHOOTER GAME

SHAPES (SQUARES, TRIANGLES,CIRCLES AND HEXAGONS (?)) FALL FROM TOP OF SCREEN
PLAYER CONTROLS A CANON AND SHOOTS SHAPES
THEY MUST SHOOT THE CORRECT SHAPE (IT IS SHOWN AT THE TOP)
IF THE SHOOT THE WRONG SHAPE OR MISS ONE THEN THEY HAVE TO ANSWER A QUESTION
(RANDOM TERM/DEF, THEY HAVE TO TYPE TERM/DEF)
SCORE IS CALCULATED FROM HOW LONG THEY WERE IN-GAME FOR
SHAPE CHANGES EVERY NOW AND THEN AND IT WILL CHANGE INCREASINGLY QUICKLY AS GAME GOES ON
SHAPES WILL ALSO GET FASTER AS GAME PROGRESSES

GAME FLOW:

START GAME -> PLAY GAME -> DIE (LEAVE SHAPE OR SHOOT SPECIAL SHAPE)
 -> ASK QUESTION -> CHECK ANSWER -> CONTINUE GAME -> DIE -> ETC...*/
	
	var quicksortShapes = function(a) {

		if (a.length <= 1) {
			return a;
		}
		
		var left = [];
		var right = [];

		var pivotIndex = Math.floor(0.5*a.length);

		for (var i=0;i<a.length;i++) {
			if (i != pivotIndex) {
				if (a[i].x <= a[pivotIndex].x) {
					left.push(a[i]);
				}
				else {
					right.push(a[i]);
				}
			}
		}

		return quicksortShapes(left).concat(a[pivotIndex]).concat(quicksortShapes(right)); 

	};

	var sign = function(n) {
		return (n == 0 ? 0 : (n > 0 ? 1 : -1));
	};
	
	function doGame() {

		function Shape(x,shape) {

			this.x = x;
			this.y = -0.5*SHAPE_SIZE;

			this.shape = shape;
			this.canvas = shapeCanvases[shape];

			this.update = function(dt) {
				if (state == PLAYING) {

					this.y += shapeSpeed * dt;

					//IF SHAPE HAS GONE OFF BOTTOM OF SCREEN...
					if (this.y - 0.5*SHAPE_SIZE >= CANVAS_HEIGHT) {

						//IF SHAPE IS NOT SPECIAL SHAPE THEN ASK THEM A QUESTION
						if (this.shape != currentSpecialShape) {
							askQuestion(MISSED_SHAPE_MESSAGE);
						}
						
						//REMOVE SHAPE
						shapes.splice(
							shapes.indexOf(this), 1
						);
					}
				}
			};

			this.draw = function() {
				myGame.c.drawImage(
					this.canvas,
					this.x - 0.5*SHAPE_SIZE,
					this.y - 0.5*SHAPE_SIZE,
					SHAPE_SIZE, SHAPE_SIZE
				);
			};

			this.die = function() {
				console.log(this.shape);
			};
		}

		function Player() {

			this.x = 0.5*CANVAS_WIDTH;
			this.v = 0;

			var shotCooldownTimer = 0;

			this.update = function(dt) {
				if (state == PLAYING) {
					/*MOVEMENT
					--------
					PLAYER STARTS AT REST
					PRESSING LEFT OR RIGHT WILL MAKE HIM ACCELERATE
					FOR 'ACCELERATION_TIME' SECONDS SO REACH 'PLAYER_SPEED'
					WHEN LEFT OR RIGHT IS RELEASED HE WILL DECELERATE FOR
					'DECELERATION_TIME' SECONDS UNTIL HE IS AT REST AGAIN*/

					//IF USER WANTS TO MOVE...
					if (myGame.keys.moveLeft.isDown || myGame.keys.moveRight.isDown) {
						var dir = myGame.keys.moveRight.isDown ? 1 : -1;

						//IF MOVING AND NOT YET REACHED MAX SPEED, ACCELERATE
						if ( Math.abs(this.v) < PLAYER_SPEED ) {
							this.v += (PLAYER_SPEED/PLAYER_ACCELERATION_TIME) * dir * dt;
						}
						//IF GOING TOO FAST SET TO MAX SPEED
						else if ( Math.abs(this.v) > PLAYER_SPEED ) {
							this.v = PLAYER_SPEED * dir;
						}
					}

					//IF USER IS NOT MOVING PLAYER BUT IS STILL MOVING, DECELERATE
					else if ( Math.abs(this.v) > 0 ) {
						
						var newV = this.v - (PLAYER_SPEED/PLAYER_DECELERATION_TIME) * sign(this.v) * dt;

						//PLAYER SHOULD DECELERATE TO STANDING, NOT BACK IN THE OTHER DIRECTION,
						//SO IF THERE HAS BEEN A SIGN CHANGE SET VELOCITY TO 0
						if (sign(newV) == sign(this.v)) {
							this.v = newV;
						}
						else {
							this.v = 0;
						}
					}

					this.x += this.v * dt;

					//IF PLAYER IS GOING OFF THE SCREEN...
					if ( this.x < 0.5*PLAYER_WIDTH || this.x > CANVAS_WIDTH - 0.5*PLAYER_WIDTH ) {
						
						//PUT PLAYER BACK IN PLACE
						this.x = this.x < 0.5*CANVAS_WIDTH ? 0.5*PLAYER_WIDTH : CANVAS_WIDTH - 0.5*PLAYER_WIDTH;

						//BOUNCE OFF THE WALL
						this.v *= -PLAYER_COEF_OF_RESTITUTION;
					}

					shotCooldownTimer += dt;
					if (myGame.keys.fire.isDown && shotCooldownTimer >= 1/FIRE_RATE) {
						shotCooldownTimer = 0;
						this.fire();
					}
				}
			};

			this.draw = function() {
				myGame.c.fillStyle = PLAYER_COLOUR;
				myGame.c.fillRect(
					this.x - 0.5*PLAYER_WIDTH,
					CANVAS_HEIGHT,
					PLAYER_WIDTH,
					-PLAYER_HEIGHT
				);
			};

			this.fire = function() {
				bullets.push( new Bullet(this.x) );
			};
		}

		function Bullet(x) {

			this.x = x;
			this.y = CANVAS_HEIGHT - PLAYER_HEIGHT;

			this.update = function(dt) {
				if (state == PLAYING) {
					this.y -= BULLET_SPEED * dt;

					//IF GONE OFF THE SCREEN...
					if (this.y <= 0) {
						this.remove();
					}

					//CHECK FOR COLLISION WITH SHAPES
					for (var i=0;i<shapes.length;i++) {
						if ( Math.sqrt(Math.pow(shapes[i].x - this.x,2) + Math.pow(shapes[i].y - this.y,2)) < BLOB_SIZE*SHAPE_SIZE ) {

							//IF THEY HAVE SHOT THE SPECIAL SHAPE ASK THEM A QUESTION
							if (shapes[i].shape == currentSpecialShape) {
								askQuestion(SHOT_SPECIAL_MESSAGE);
							}

							shapes.splice(i,1);
							this.remove();
							break;
						}
					}
				}
			};

			this.draw = function() {
				myGame.c.fillRect(
					this.x - 2,
					this.y - 2,
					4, 4
				);
			};

			this.remove = function() {
				bullets.splice(
					bullets.indexOf(this), 1
				);
			};

		}

		var allTerms = decode(cipheredTerms);

		//CONSTANTS
		const BACKGROUND = "black";
		const BORDER_COLOUR = "white";
		const FONT_COLOUR = "white";
		const PLAYER_COLOUR = "white";
		const SCORE_COLOUR = "white";

		const SCORE_FONT = "20px Arial";

		//GAME SETTINGS
		const NEW_SHAPE_INTERVAL = 1; //TIME BETWEEN SHAPES SPAWNING
		const SHAPE_SIZE = 70;
		const BASE_SHAPE_SPEED = 50;
		const SHAPE_SPEED_INCREASE = 13; //AMOUNT THAT SPEED GOES UP EACH ROUND
		const BLOB_SIZE = 0.5; //PERCENTAGE OF SHAPE RADIUS THAT BLOB'S RADIUS SHOULD BE

		const BULLET_SPEED = 300;
		const PLAYER_SPEED = 400;
		const PLAYER_DECELERATION_TIME = 0.25;
		const PLAYER_ACCELERATION_TIME = 0.25;
		const PLAYER_COEF_OF_RESTITUTION = 0.9; //FOR COLLISION BETWEEN PLAYER AND WALL
		const PLAYER_WIDTH = 10;
		const PLAYER_HEIGHT = 20;
		const FIRE_RATE = 4; //SHOTS PER SECOND

		const CANVAS_WIDTH = 600;
		const CANVAS_HEIGHT = 600;

		const SPECIAL_SHAPE_SHOW_TIME = 2;
		const MAX_ROUND_TIME = 60;
		const SCORE_PER_SECOND = 5; //AMOUNT THAT SCORE IS INCREASED BY EACH SECOND
		const CORRECT_ANSWER_BONUS = 20;

		//MESSAGES
		const MAX_ROUND_MESSAGE = "Nice shooting";
		const SHOT_SPECIAL_MESSAGE = "You shot the special shape!";
		const MISSED_SHAPE_MESSAGE = "You missed a shape!";

		//STATE CONSTANTS
		const NOT_STARTED = 0;
		const PLAYING = 1;
		const QUESTION = 2;
		const POST_QUESTION = 3;
		const END_GAME = 4;

		//SHAPE CONSTANTS
		const TRIANGLE = 0;
		const PENTAGON = 1;
		const DIAMOND = 2;
		const CIRCLE = 3;

		var getShapeCanvas = function(sides,colour) {
			var canvas = document.createElement("canvas");

			var shapeSize = 100;
			canvas.width = 2*shapeSize;
			canvas.height = 2*shapeSize;

			var ctx = canvas.getContext("2d");
			ctx.fillStyle = colour;

			ctx.beginPath();
			for (var i=0;i<sides;i++) {

				//COORDS IF SHAPE IS CENTERED ON ORIGIN
				var x = shapeSize*Math.cos(i*2*Math.PI/sides);
				var y = shapeSize*Math.sin(i*2*Math.PI/sides);
				
				//ROTATE 90 CLOCKWISE
				var temp = x;
				x = y;
				y = -temp;

				//PUT IN MIDDLE OF CANVAS
				x += 0.5*canvas.width;
				y += 0.5*canvas.height;

				ctx.lineTo(x,y);
			}
			ctx.fill();

			return canvas;
		};

		//PRE-DRAWN CANVASES FOR DIFFERENT SHAPES
		var shapeCanvases = [
			getShapeCanvas(3,"#70D82C"),
			getShapeCanvas(5,"#3247A8"),
			getShapeCanvas(4,"#F6BC32"),
			getShapeCanvas(200,"#E12D5A")
		];

		//GAME VARS
		var state = NOT_STARTED;
		var timer;
		var roundTimer;
		var player;
		var lastXCoord;
		var shapes;
		var bullets;
		var currentSpecialShape;
		var showingSpecialShape;
		var quizData;
		var shapeSpeed;
		var score;

		//MAIN CANVAS
		var myGame = new Game(CANVAS_WIDTH,CANVAS_HEIGHT);
		myGame.update = function(dt) {
			if (state == PLAYING) {
				
				//TIMER FOR ADDING NEW SHAPE
				timer += dt;
				if (timer >= NEW_SHAPE_INTERVAL) {
					addShape();
					timer -= NEW_SHAPE_INTERVAL;
				}

				//TIMER FOR STARTING NEW ROUND
				roundTimer += dt;
				if (roundTimer > MAX_ROUND_TIME) {
					askQuestion(MAX_ROUND_MESSAGE);
				}

				//INCREASE SCORE
				score += SCORE_PER_SECOND * dt;

				//UPDATE OTHER THINGS
				player.update(dt);
				for (var i=0;i<shapes.length;i++) {
					shapes[i].update(dt);
				}
				for (var i=0;i<bullets.length;i++) {
					bullets[i].update(dt);
				}
			}

		};
		myGame.draw = function(dt) {

			if (state == PLAYING ) {
				//CLEAR THE CANVAS
				this.c.fillStyle = BACKGROUND;
				this.c.fillRect(0, 0, this.canvas.width, this.canvas.height);
				
				//DRAW BORDER
				this.c.strokeStyle = BORDER_COLOUR;
				this.c.strokeRect(0, 0, this.canvas.width, this.canvas.height);

				//SHOW SPECIAL SHAPE IF REQUIRED
				if (showingSpecialShape) {
					this.c.save();
					this.c.globalAlpha = 0.5;
					this.c.drawImage(
						shapeCanvases[currentSpecialShape],
						0.5*CANVAS_WIDTH - 100,
						50,
						200,200
					);
					this.c.restore();
				}

				//DRAW OTHER THINGS
				player.draw();
				for (var i=0;i<shapes.length;i++) {
					shapes[i].draw();
				}
				for (var i=0;i<bullets.length;i++) {
					bullets[i].draw();
				}

				//SHOW SCORE
				this.c.fillStyle = SCORE_COLOUR;
				this.c.fillText(
					"Score: " + printableScore(score),
					2, this.canvas.height
				);
			}

		};

		//CONTROLS
		myGame.setControl("space","woohoo",function(){
			if (state == NOT_STARTED) {
				startGame();
			}
			else if (state == POST_QUESTION) {
				continueGame();
			}
			else if (state == END_GAME) {
				$("#end").hide();
				startGame();
			}
		});
		myGame.setControl("space","fire"); //ADDED SPACE AGAIN TO GIVE IT NAME FOR FIRING
		myGame.setControl("left","moveLeft");
		myGame.setControl("right","moveRight");
		myGame.setControl("d","debug",function(){
		});

		//SET UP FOR PRINTING SCORE ON CANVAS
		myGame.c.font = SCORE_FONT;
		myGame.c.textBaseline = "bottom";

		var startGame = function(){

			//RESET GAME VARS
			state = PLAYING;
			player = new Player();
			timer = 0;
			roundTimer = 0;
			lastXCoord = 0.5*CANVAS_WIDTH;
			shapes = [];
			bullets = [];
			showingSpecialShape = false;
			quizData = getMultipleChoiceQuestions({
				terms: allTerms,
				options: 1, //DOESN'T MATTER,
				randomOrder: true,
				questionType: "term"
			});
			shapeSpeed = BASE_SHAPE_SPEED;
			score = 0;

			//HIDE INTRO
			$("#intro").hide();

			newRound();
		};

		var endGame = function() {
			state = END_GAME;
			var ps = printableScore(score);
			$("#end_result").html(
				"Your score was " + ps
			);
			$("#end").show();

			saveScores(ps);
		};

		var printableScore = function(n) {
			var s = n.toString();
			var i = s.indexOf(".");
			return i == -1 ? s : s.substr(0,i+3);
		};

		var addShape = function() {
			var r = Math.floor(Math.random() * 4);
			
			//MAKE SURE NEW SHAPE IS NOT TOO CLOSE TO THE LAST ONE
			var x = lastXCoord;
			while (Math.abs(x - lastXCoord) < SHAPE_SIZE) {

				//RANDOM X COORD, BUT NOT SO THAT SHAPE WILL BE OFF THE SCREEN
				x = 0.5*SHAPE_SIZE + Math.random()*(CANVAS_WIDTH - SHAPE_SIZE);

			}
			lastXCoord = x;

			var shape = new Shape(x,r);
			shapes.push(shape);

			//shapes = quicksortShapes(shapes);
		};

		var newRound = function() {

			//RESET ROUND TIMER AND SHAPES AND BULLETS
			shapes = [];
			bullets = [];
			roundTimer = 0;

			//INCREASE SHAPE SPEED
			shapeSpeed += SHAPE_SPEED_INCREASE;

			//GET NEW SPECIAL SHAPE
			do {
				var r = Math.floor(Math.random()*4);
			} while (r == currentSpecialShape);

			currentSpecialShape = r;

			showingSpecialShape = true;
			window.setTimeout(function(){
				showingSpecialShape = false;
			},SPECIAL_SHAPE_SHOW_TIME*1000);
		};

		var askQuestion = function(message) {
			state = QUESTION;

			$("#message").html(message);
			$("#question_div, #question_div textarea").show();
			$("#question").html("Q: " + quizData.questions[0]);
			$("#question_div textarea").focus();
		};

		var continueGame = function() {

			$("#question_div, #continue, #result").hide();

			//FINISH THE GAME IS THERE ARE NO QUESTIONS LEFT
			if (quizData.questions.length == 0) {
				endGame();
			}
			//OTHERWISE CARRY ON
			else {
				state = PLAYING;
				newRound();
			}
		};

		var checkAnswer = function() {
			var answer = $("#question_div textarea").val().trim().replace(/( ){2,}/," ");

			if (!answer) {
				return false;
			}

			$("#question_div textarea").hide();

			if (answer.toUpperCase() == quizData.answers[0].toUpperCase()) {
				$("#result").html("Correct!");
				score += CORRECT_ANSWER_BONUS;
			}
			else {
				$("#result").html("Incorrect. The correct answer was <span class='bold'>'" + quizData.answers[0] + "'</span>");
			}

			//REMOVE ANSWERED QUESTION
			quizData.questions.splice(0,1);
			quizData.answers.splice(0,1);

			$("#question_div textarea").val("");

			$("#continue, #result").show();
			state = POST_QUESTION;
		};

		myGame.run();

		$("body").css({
			background: BACKGROUND,
			overflow: "hidden"
		});

		$("div").css({
			width: "400px",
			margin: "100px auto",
			textAlign: "center",
			color: FONT_COLOUR,
		});

		$("canvas").css({
			position: "absolute",
			top: 0.5*(window.innerHeight - CANVAS_HEIGHT) + "px",
			left: 0.5*(window.innerWidth - CANVAS_WIDTH) + "px",
			zIndex:"-1"
		});

		$("#question_div, #continue, #end").hide();

		$("textarea").css({
			width: "400px",
			height: "200px"
		}).keypress(function(e){
			if (e.keyCode == 13) {
				checkAnswer();
				return false;
			}
		});

	}

	doGame();

</script>