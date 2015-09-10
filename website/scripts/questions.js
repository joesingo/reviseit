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

var getMultipleChoiceQuestions = function(settings) {
	//SETTINGS:
	//terms - THIS IS ARRAY CONTAINING ARRAYS CONTAINING TERMS AND DEFS. E.G. [ ["t1", "d1"] , ["t2", "d2"] ]
	//options - THE NUMBER OF OPTIONS TO CHOOSE FROM
	//randomOrder - RANDOM ORDER OR ORDER IN terms?
	//questionType - WHAT SHOULD BE ASKED AS QUESTION? "term", "def" OR "mix".

	var allTerms = settings.terms.slice();
	
	var questions = [];
	var options = [];
	var answers = [];
	var IDs = [];
	
	//SHUFFLE TERMS IF NEEDS TO BE RANDOM ORDER
	if (settings.randomOrder) shuffleArray(allTerms,100);
	
	//MAKE 2 PARALLEL ARRAYS FOR JUST TERMS AND JUST DEFS
	var terms = [];
	var defs = [];
	$.each(allTerms,function(i){
		terms.push( allTerms[i][0] );
		defs.push( allTerms[i][1] );
	});

	//STORE TERM IDS IN ARRAY PARALLEL TO QUESTIONS AND ANSWERS
	for (var i=0;i<allTerms.length;i++) {
		IDs.push(allTerms[i][2]);
	}
		
	$.each(terms,function(i){
		
		var questionArray = [];
		var answerArray = [];
		
		//WORK OUT WHICH IS Q AND WHICH IS A FROM QUESTION TYPE
		if (settings.questionType == "term") {
			questionArray = terms.slice();
			answerArray = defs.slice();
		}
		else if (settings.questionType == "def") {
			questionArray = defs.slice();
			answerArray = terms.slice();
		}
		else if (settings.questionType == "mix") {
			var r = Math.floor(Math.random() * 2);
			questionArray = r == 0 ? terms.slice() : defs.slice();
			answerArray = r == 1 ? terms.slice() : defs.slice();
		}
		
		//GET Q AND A
		var question = questionArray[i];
		var correctAnswer = answerArray[i];
	
		//REMOVE CORRECT ANSWER FROM ANSWERS ARRAY TO MAKE SURE CORRECT
		//ANSWER DOES NOT APPEAR TWICE
		answerArray.splice(
			$.inArray( correctAnswer, answerArray ), 1
		);
		
		//SHUFFLE AND GET SOME OFF TOP TO GET RANDOM OPTIONS
		shuffleArray(answerArray,100);
		var thisOptions = answerArray.slice(0,settings.options - 1);
		
		//ADD CORRECT ANSWER TO GET FULL SET OF OPTIONS
		thisOptions.push(correctAnswer);
		shuffleArray(thisOptions,100);
		
		questions.push(question);
		options.push(thisOptions);
		answers.push(correctAnswer);
	});
	
	return {
		questions: questions,
		options: options,
		answers: answers,
		IDs: IDs
	};
}