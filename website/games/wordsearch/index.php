<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>

<div id="content">

	<h2>Wordsearch</h2>

	<div id="game_area"></div>
	<div id="end_game_div">Wordsearch complete, well done!</div>
	<p><span id="words_left" class='bold'></span> words remaining</p>
	<p id="timer">00:00</p>
	<div id="words_found_div">
		Words found:
		<ul></ul>
	</div>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>
	
	<script type="text/javascript" src="/scripts/game.js"></script>
	<script type="text/javascript">

		function playGame() {

			var allTerms = decode(cipheredTerms);

			const directions = [
				[1,0],
				[1,1],
				[0,1],
				[-1,1],
				[-1,0],
				[-1,-1],
				[0,-1],
				[1,-1]
			];
			const alphabet = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
			const BLANK_SPACE = "nothing";
			const FAKE_WORD_PROBABILITY = 0.8;

			var selectionStart = [];
			var selection = "";

			var gridSize = allTerms.length < 10 ? 17 : 34;

			/* - - - - */

			var replaceAll = function(word,s1,s2) {
				do {
					word = word.replace(s1,s2);
				} while (word.indexOf(s1) != -1);

				return word;
			};

			var selectWord = function(x1,y1,x2,y2,className) {
				//SELECT THE LINE OF LETTERS AND GIVE EACH LETTER CLASS className

				//UN-SELECT THE HOVERED LETTERS
				$("#game_area td.hovered").removeClass("hovered");

				//GET SELECTION DIRECTION
				var xDir, yDir;

				if (x2 == x1) xDir = 0;
				else if (x2 > x1) xDir = 1;
				else xDir = -1;

				if (y2 == y1) yDir = 0;
				else if (y2 > y1) yDir = 1;
				else yDir = -1;

				selection = "";
				var thisx = x1;
				var thisy = y1;

				//GO THROUGH LINE AN ADD TO SELECTION STRING
				while ( !(thisx == x2 + xDir && thisy == y2 + yDir) ) {
					selection += grid[thisx][thisy];
					$("#game_area td[data-x=" + thisx + "][data-y=" + thisy + "]").addClass(className);

					thisx += xDir;
					thisy += yDir;
				}

			}

			var putWordInGrid = function(word) {

				//FIND A PLACE FOR WORD TO GO!
				outerloop: while (true) {

					//GET RANDOM DIRECTION
					var direction = directions[ Math.floor(Math.random() * directions.length) ];

					//GET RANDOM STARTING COORDS
					var ux = Math.floor( Math.random() * gridSize );
					var uy = Math.floor( Math.random() * gridSize );

					//CHECK THAT THIS SPACE WILL BE OKAY
					innerloop: for (var i=0;i<t.length;i++) {
						var x = ux + direction[0] * i;
						var y = uy + direction[1] * i;

						//IF NOT VALID SPACE...
						if (!( x < gridSize && x >= 0 && y < gridSize && y >= 0 && (grid[x][y] === word[i] || grid[x][y] == BLANK_SPACE) )) {
							//...TRY AGAIN
							continue outerloop;
						}
					}

					//IF HAVE GOT TO THIS POINT SPACE IS OKAY, SO BREAK LOOP
					break;
				}

				//ACTUALLY PUT WORD IN GRID
				for (var i=0;i<word.length;i++) {
					x = ux + direction[0] * i;
					y = uy + direction[1] * i;

					grid[x][y] = word[i];
				}
			}

			/* - - - - -*/

			//MAKE GRID ARRAY
			var grid = [];
			for (var i=0;i<gridSize;i++) {
				grid.push([]);
				for (var j=0;j<gridSize;j++) {
					grid[i].push(BLANK_SPACE);
				}
			}

			//GET A COPY OF TERMS
			var terms = [];
			for (var i=0;i<allTerms.length;i++) {
				terms.push( replaceAll(allTerms[i][0]," ","").toUpperCase() );
			}

			//PUT WORDS IN GRID!
			for (var i=0;i<terms.length;i++) {
				var t = terms[i];

				if ( t.length <= gridSize ) {

					putWordInGrid(t);

					//TRY TO PUT A FAKE WORD IN
					if (Math.random() < FAKE_WORD_PROBABILITY) {

						//WILL HOLD INDEXES OF ALL THE VOWELS (IF ANY) IN THE WORD
						var vowelPositions = [];
						
						//GO THROUGH WORD AND FIND VOWELS
						for (var j=0;j<t.length;j++) {
							if (t[j].match(/[AEIOU]/)) {
								vowelPositions.push(j);
							}
						}

						if (vowelPositions.length > 0) {

							//CHOOSE A RANDOM VOWEL...
							var r = Math.floor( Math.random() * vowelPositions.length );
							var chosenVowelPos = vowelPositions[r];

							var vowels = ["A","E","I","O","U"];
							var newVowel;

							do {
								var r = Math.floor( Math.random() * vowels.length );
								newVowel = vowels[r];
							} while (newVowel == t[chosenVowelPos])

							var fakeWord = t.substring(0,chosenVowelPos) + newVowel + t.substring(chosenVowelPos + 1);
							putWordInGrid(fakeWord);

						}
					}

				}
			}

			//FILL GRID WITH RANDOM LETTERS AND CONSTRUCT TABLE
			var t = "<table>";
			for (var i=0;i<gridSize;i++) {
				t += "<tr>";
				for (var j=0;j<gridSize;j++) {
					t += "<td data-x='" + j + "' data-y='" + i + "'>";
					if (grid[j][i] === BLANK_SPACE) {
						grid[j][i] = alphabet[ Math.floor( Math.random() * alphabet.length ) ];
					}
					t += grid[j][i];
					t += "</td>";
				}
				t += "</tr>";
			}
			t += "</table>";

			//ADD TABLE TO PAGE!
			$("#game_area").append(t);

			//SHOW HOW MANY WORDS THEY HAVE LEFT TO FIND
			$("#words_left").html(terms.length);

			//DO TIMER
			var timer = 0;
			window.timerInterval = setInterval(function() {
				timer += 1;

				var minutes = Math.floor(timer / 60);
				var seconds = timer - (minutes * 60);

				if (minutes < 10) minutes = "0" + minutes;
				if (seconds < 10) seconds = "0" + seconds;

				$("#timer").html(
					minutes + ":" + seconds
				);
			},1000);

			//WHEN HOVERING OVER A LETTER...
			$("#game_area td").hover(function() {

				var x = $(this).data("x");
				var y = $(this).data("y");	

				//IF THEY HAVE STARTED SELECTION...
				if (selectionStart.length != 0) {

					var ux = selectionStart[0];
					var uy = selectionStart[1];

					var dx = x - ux;
					var dy = y - uy;

					if ( Math.abs(dx) > Math.abs(dy) ) {
						y = uy;
					}
					else if ( Math.abs(dx) < Math.abs(dy) ) {
						x = ux;
					}

					//SELECT WORD AND MAKE LETTERS HOVERED
					selectWord(ux,uy,x,y,"hovered");
				}

			},function() {return false;});

			//WHEN CLICKING A LETTER...
			$("#game_area td").click(function() {
				
				var x = $(this).data("x");
				var y = $(this).data("y");	

				//IF HAVE NOT STARTED SELECTION...
				if (selectionStart.length == 0) {
					//...START THE SELECTION!
					selectionStart = [x,y];
					$(this).addClass("hovered");
				}
				else {

					var termIndex = terms.indexOf(selection);

					//IF THEY SELECTED A PROPER WORD...
					if (termIndex != -1) {

						//MAKE ALL HOVERED LETTERS SELECTED
						$("#game_area td.hovered").removeClass("hovered").addClass("selected");

						//REMOVE TERM
						terms.splice(termIndex, 1);
						$("#words_left").html(terms.length);

						//ADD TO WORDS FOUND LIST
						$("#words_found_div ul").append("<li class='bold'>" + selection + "</li>");

						if (terms.length == 0) {
							window.clearInterval(window.timerInterval);
							$("#end_game_div").show();
						}
					}
					else {
						//NOT A PROPER WORD, UN-SELECT LETTERS
						$("#game_area td.hovered").removeClass("hovered")						
					}

					//RESET SELECTION
					selection = "";
					selectionStart = [];
				}

			});

		}

		$("head").append("<link rel='stylesheet' href='/games/wordsearch/style.css' />");
		playGame();

	</script>

</div>