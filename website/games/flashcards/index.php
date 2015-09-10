<?php include("$_SERVER[DOCUMENT_ROOT]/includes/header.php"); ?>

<div id="content">

	<h2>Flashcards</h2>

	<div id="flashcard"><span></span></div>

	<button id="prev_button">Previous</button>
	<button id="next_button">Next</button>

	<hr />

	<section>
		<label><input type="checkbox" id="random_checkbox" /> Random order</label>
		<br />
		<p>
			<label><input type="radio" name="question_type" value="term" checked>Show term first</label><br />
			<label><input type="radio" name="question_type" value="def">Show definition first</label><br />
			<label><input type="radio" name="question_type" value="mix">Random</label>
		</p>
	</section>

	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/footer.php"); ?>

	<script type="text/javascript" src="/scripts/questions.js"></script>
	<script type="text/javascript">

		var allTerms = decode(cipheredTerms);

		$("#flashcard").css({
			display: "table",
			width: 400,
			height:300,
			border:"2px solid black",
			margin: "20px auto",
			fontSize: "2em"
		});
		$("#flashcard span").css({
			display: "table-cell",
			verticalAlign: "middle",
			padding:"10px"
		});
		$("#flashcard").click(function(){
			currentSide = (currentSide + 1) % 2; //SWAP CURRENT SIDE
			updateFlashcard();
		});

		$("#prev_button").click(function(){
			currentCard = currentCard - 1 < 0 ? cards.length - 1 : currentCard - 1;
			currentSide = defaultSide;
			updateFlashcard();
		});
		$("#next_button").click(function(){

			//IF THEY HAVE REACHED THE END OF THE RANDOM CARDS START AGAIN
			//(THIS IS BECAUSE IF A RANDOM CARD WAS CHOSEN EACH TIME YOU MIGHT NOT GET ONE OF THEM - THIS
			// INSURES THAT ALL CARDS ARE SEEN ONCE EACH GO BUT PATTERN IS NOT THE SAME EVERY TIME)
			if (randomOrder && currentCard == cards.length -1) {
				start();
			}
			else {
				currentCard = currentCard + 1 < cards.length ? currentCard + 1 : 0;
				currentSide = defaultSide;
				updateFlashcard();
			}
		});
		$("#random_checkbox").change(function(){
			randomOrder = randomOrder ? false : true; //TOGGLE RANDOM ORDER
			start();
		});
		$("input[name=question_type]").click(function(){
			switch ($(this).val()) {
				case "term":
					defaultSide = TERM;
					break;
				case "def":
					defaultSide = DEF;
					break;
				case "mix":
					defaultSide = MIX;
			}
			currentSide = defaultSide;
			updateFlashcard();
		});

		//QUESTION TYPE CONSTANTS
		const TERM = 0;
		const DEF = 1;
		const MIX = 2;

		var cards = [];
		var currentCard;
		var currentSide;
		var defaultSide = TERM;
		var randomOrder = false;

		var updateFlashcard = function() {

			//RANDOM SIDE NEEDS TO CHANGE EACH UPDATE...
			if (currentSide == MIX) {
				var r = [0,1][Math.floor(Math.random()*2)];
				$("#flashcard span").html(cards[currentCard][r]);
			}
			else {
				$("#flashcard span").html(cards[currentCard][currentSide]);
			}
		}

		var start = function() {
			var quizData = getMultipleChoiceQuestions({
				terms: allTerms,
				options: 1, //DOESN'T MATTER FOR FLASHCARDS...
				randomOrder: randomOrder,
				questionType: "term"
			});

			cards = [];
			for (var i=0;i<quizData.questions.length;i++) {
				cards.push(
					[quizData.questions[i], quizData.answers[i]]
				);
			}

			currentCard = 0;
			currentSide = defaultSide;

			updateFlashcard();
		};

		start();

	</script>
	
</div>