<?php

function encode($s,$key) {
	//CIPHER $s USING VIGNERE CIPHER WITH $key AS THE KEY

	$o = "";

	for ($i=0;$i<strlen($s);$i++) {

		$char = substr($s, $i, 1);
		$pos = alphabet_position($char);

		if ($pos !== false) {
			$key_char = substr($key, $i, 1);
			$char_pos = (alphabet_position($key_char) + $pos) % 52;
			$char = char_at_position($char_pos);
		}

		$o .= $char;
	}
	return $o;
}

function alphabet_position($char) {
	//RETURN POSITION IN THE ALPHABET, WITH ALPHABET GOING A,B,C,D...X,Y,Z,a,b,c,d...,x,y,z AND POSITIONS STARTING AT 0
	$asc = ord($char);

	if ($asc >= 65 and $asc <= 90) {
		return $asc - 65;
	}
	else if ($asc >= 97 and $asc <= 122) {
		return $asc - 71;
	}
	else {
		return false;
	}
}

function char_at_position($n) {
	//RETURN CHARACTER AT POSITION $n ACCORDING TO LETTER->NUMBER SCHEME DEFINED ABOVE
	if ($n >= 0 and $n <= 25) {
		return chr($n + 65);
	}
	else if ($n >= 26 and $n <= 51) {
		return chr($n + 71);
	}
	else {
		return false;
	}
}

function random_key($length) {
	//A RANDOM KEY $length CHARACTERS LONG
	$o = "";
	for ($i=0;$i<$length;$i++) {
		$r = mt_rand(0,51);
		$o .= char_at_position($r);
	}
	return $o;
}

include("$_SERVER[DOCUMENT_ROOT]/includes/set_auth.php");

if (isset($_GET["gid"])) {
	$game_id = mysqli_real_escape_string($m, $_GET["gid"]);

	$games_query = "SELECT name, path, min_terms FROM games WHERE id='$game_id'";
	$terms_query = "SELECT id, term, def FROM terms WHERE setid='$set_id'";

	$games_res = $m->query($games_query)->fetch_all(MYSQL_ASSOC);
	$terms_res = $m->query($terms_query)->fetch_all(MYSQL_ASSOC);

	if (!$games_res) {
		header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=badGID");
	}
	if (!$terms_res) {
		header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=termError");
	}

	if (count($terms_res) >= $games_res[0]["min_terms"]) {
		$game_name = $games_res[0]["name"];
		$game_path = $games_res[0]["path"];
	}
	else {
		header("Location: http://$_SERVER[HTTP_HOST]/view_set/?sid=$set_id&e=tooFewTerms&termsRequired=" . $games_res[0]["min_terms"]);
	}
}
else {
	header("Location: http://$_SERVER[HTTP_HOST]/error.php?e=noGID");
}

?>

<!DOCTYPE html>
<html>

<head>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/includes/head.php"); ?>

	<title><?php echo $games_res[0]["name"]; ?> | Revise it</title>

	<link rel="stylesheet" type="text/css" href="/styles/main_style.css" />

	<script type="text/javascript" src="/scripts/jquery-1.11.1.min.js"></script>
	<script type="text/javascript">

		//(SEE PHP ABOVE FOR DESRIPTION OF WHAT CIPHER RELATED FUNCTIONS DO FUNCTION DO)

		function alphabetPosition(chr) {
			var asc = chr.charCodeAt(0);

			if (asc >= 65 && asc <= 90) {
				return asc - 65;
			}
			else if (asc >= 97 && asc <= 122) {
				return asc - 71;
			}
			else {
				return false;
			}
		}

		function charAtPosition(n) {
			if (n >= 0 && n <= 25) {
				return String.fromCharCode(n + 65);
			}
			else if (n >= 26 && n <= 51) {
				return String.fromCharCode(n + 71);
			}
			else {
				return false;
			}
		}

		function decodeWord(s,key) {
			var o = "";

			for (var i=0;i<s.length;i++) {
				var chr = s[i];
				var pos = alphabetPosition(chr);

				if (pos !== false) {

					var chrPos = pos - alphabetPosition(key[i]);
					if (chrPos < 0) {
						chrPos += 52;
					}
					var chr = charAtPosition(chrPos);
				}

				o += chr;
			}
			return o;		
		}

		function decode(t) {
			var t2 = [];
			for (var i=0;i<t.length;i++) {
				t2.push(
					[decodeWord(t[i][0], t[i][3]), decodeWord(t[i][1], t[i][3])]
				);
			}
			return t2;
		}

		var saveScores = function(score,beforeSend,callback) {
			$.ajax({
				url: "/ajax/save_scores.php",
				method: "GET",
				data: {
					score: score,
					sid: <?php echo $set_id; ?>,
					gid: <?php echo $game_id; ?>
				},
				beforeSend: beforeSend || function(){}
			}).done(function(res){
				if (callback) {
					callback(res);
				}
			});
		}

		var cipheredTerms = <?php
			$output = "[";
			foreach ($terms_res as $i) {
				$key = random_key( max( strlen($i["term"]), strlen($i["def"]) ) );

				$term = encode($i["term"],$key);
				$def = encode($i["def"],$key);

				$output .= "['$term','$def','$i[id]','$key'],";
			}
			$output = substr($output, 0, -1);
			$output .= "]";

			echo $output;
		?>;

	</script>
			

</head>

<body>
	
	<?php include("$_SERVER[DOCUMENT_ROOT]/games/$game_path/index.php"); ?>

</body>

</html>
