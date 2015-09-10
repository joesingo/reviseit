const CANVAS_WIDTH = 450;
const CANVAS_HEIGHT =450;
const LEFT_PADDING = 20;
const TOP_PADDING = 20;
const RIGHT_PADDING = 20;
const BOTTOM_PADDING = 20;

const FONT_SIZE = 16;
const BLOB_RADIUS = 5;

const AXES_COLOUR = "black";
const LINE_COLOUR = "gray";
const BLOB_COLOUR = "green";
const BLOB_COLOUR_HOVERED = "yellow";

/* - - - - - */

function getTimestamp(d) {
	//CONVERT FROM MYSQL DATE FORMAT TO UNIX TIMESTAMP
	
	var year = parseInt(d.substr(0,4));
	var month = parseInt(d.substr(5,2)) - 1;
	var day = parseInt(d.substr(8,2));
	var hour = parseInt(d.substr(11,2));
	var minute = parseInt(d.substr(14,2));
	var second = parseInt(d.substr(17,2));
	
	var nd = new Date(year,month,day,hour,minute,second);
	return nd.getTime();
}

function sortData(data) {		
	for (var i=0;i<data.length;i++) {
		
		//CONVERT DATE SO SORTING IS EASY
		data[i].date = getTimestamp(data[i].date);
		
		//GET HIGHEST SCORE
		if (data[i].score > highestScore) {
			highestScore = data[i].score;
		}
	}
	
	//RETURN ARRAY SORTED BY DATE
	return dateQuicksort(data)
}

function dateQuicksort(a) {
	if (a.length <= 1){
		return a;
	}

	var pivotIndex = Math.ceil(a.length / 2);

	var left = [];
	var right = [];

	for (var i=0;i<a.length;i++) {
		if (i != pivotIndex) {
			if (a[i].date <= a[pivotIndex].date) {
				left.push(a[i]);
			}
			else {
				right.push(a[i]);
			}
		}
	}

	return dateQuicksort(left).concat(a[pivotIndex]).concat(dateQuicksort(right));
}

function getCoordinates(p) {
	//RETURNS CANVAS COORDINATES FOR A SCORE/DATE
	
	var graphWidth = CANVAS_WIDTH - LEFT_PADDING - RIGHT_PADDING;
	var graphHeight = CANVAS_HEIGHT - TOP_PADDING - BOTTOM_PADDING;
	
	var x = LEFT_PADDING + graphWidth * ( (p.date - lowestDate) / dateRange )
	var y = CANVAS_HEIGHT - BOTTOM_PADDING - ( graphHeight * (p.score / highestScore) );
	
	return [x,y];
}

function getReadableDate(timestamp) {
	//RETURNS NICELY FORMATTED DATE FROM UNIX TIMESTAMP
	var d = new Date(timestamp);
	
	var date = d.getDate();
	var month = d.getMonth() + 1;
	var year = d.getFullYear();
	
	if (date < 10) date = "0" + date;
	if (month < 10) month = "0" + month;
	
	return date + "-" + month + "-" + year;
}

function drawBlob(coords,hovered) {
	c.beginPath();
	c.arc(coords[0],coords[1],BLOB_RADIUS,0,Math.PI * 2,false);
	c.fillStyle = hovered == true ? BLOB_COLOUR_HOVERED : BLOB_COLOUR;
	c.fill();
}

function getGraph(data,container) {

	if (data.length < 2) {
		container.html("Not enough scores to show a graph!");
		return false;
	}

	highestScore = data[0].score;
	data = sortData(data);
	lowestDate = data[0].date;
	highestDate = data[data.length - 1].date;
	dateRange = highestDate - lowestDate;

	//MAKE CANVAS
	var canvas = document.createElement("canvas");
	canvas.width = CANVAS_WIDTH;
	canvas.height = CANVAS_HEIGHT;
	c = canvas.getContext("2d");
			
	//DRAW VERTICAL AXIS...
	c.beginPath();
	c.moveTo(LEFT_PADDING,CANVAS_HEIGHT - BOTTOM_PADDING);
	c.lineTo(LEFT_PADDING,TOP_PADDING);
	c.strokeStyle = AXES_COLOUR;
	c.stroke();

	//DRAW HORIZONTAL AXIS...
	c.beginPath();
	c.moveTo(LEFT_PADDING,CANVAS_HEIGHT - BOTTOM_PADDING);
	c.lineTo(CANVAS_WIDTH - RIGHT_PADDING,CANVAS_HEIGHT - BOTTOM_PADDING);
	c.stroke();

	//LABEL AXES...
	c.textBaseline = "middle";
	c.textAlign = "center";
	c.font = FONT_SIZE + "px Arial";
	c.fillStyle = AXES_COLOUR;

	c.save();
	c.translate(LEFT_PADDING * 0.5, (CANVAS_HEIGHT - TOP_PADDING - BOTTOM_PADDING) * 0.5 + TOP_PADDING);
	c.rotate(-Math.PI * 0.5);
	c.fillText("Score",0,0);
	c.restore();

	c.save();
	c.translate((CANVAS_WIDTH - LEFT_PADDING - RIGHT_PADDING) * 0.5 + LEFT_PADDING, CANVAS_HEIGHT - (BOTTOM_PADDING * 0.5));
	c.fillText("Date",0,0);
	c.restore();
	
	//GET POINTS AND DRAW LINES
	var points = [];
	c.beginPath();
	for (var i=0;i<data.length;i++) {
		var coords = getCoordinates(data[i]);
		c.lineTo(coords[0],coords[1]);
		points.push(coords);
	}
	c.strokeStyle = LINE_COLOUR;
	c.stroke();

	//DRAW BLOBS
	for (var i=0;i<points.length;i++) {
		drawBlob(points[i]);
	}

	var hoveredBlob = null; //STORES THE COORDINATES OF THE BLOB CURRENTLY BEING HOVERED OVER

	//SEE IF MOUSE IS HOVERED OVER A BLOB TO MAKE GRAPH RESPONSIVE AND COOL
	$(canvas).mousemove(function(e){

		var offset = $(this).offset();
		var x = e.pageX - offset.left;
		var y = e.pageY - offset.top;
		
		var nearBlob = false;
		
		for (var i=0;i<points.length;i++) {
			if ( Math.sqrt( Math.pow(points[i][0] - x, 2) + Math.pow(points[i][1] - y, 2) ) <= BLOB_RADIUS ) {
				
				nearBlob = true;
				hoveredBlob = points[i];
				
				$("#canvas_wrapper div").html("<b>" + data[i].score + "</b> (" + getReadableDate(data[i].date) + ")"); //SHOW DATE AND SCORE IN DIV
				$("#canvas_wrapper div").css({ //PUT DIV UNDER MOUSE
					top: e.pageY,
					left: e.pageX
				});
				$("#canvas_wrapper div").show();
				
				//DRAW HOVERED BLOB
				drawBlob(points[i],true);
				
				break;
			}
		}
		
		//IF MOUSE IF NOT OVER BLOB BUT WAS LAST TIME...
		if (!nearBlob && hoveredBlob != null) {
			
			//REDRAW BLOB AS NON-HOVERED
			drawBlob(hoveredBlob);
			
			hoveredBlob = null;
			
			$("#canvas_wrapper div").html(""); //RESET DIV
			$("#canvas_wrapper div").hide();
		}
	});

	container.html("<div id='canvas_wrapper'></div>");
	$("#canvas_wrapper").append(canvas);
	$("#canvas_wrapper").append("<div></div>");
	
	//POPUP DIV STYLING...
	$("#canvas_wrapper div").css({
		"position": "absolute",
		"display": "none",
		"border": "1px solid blue",
		"background": "white",
		"font-size": "15px",
		"padding": "10px",
	});
	
	$("#canvas_wrapper div").mouseout(function(){
		//THIS IS TO FIX ISSUE WHERE YOU CAN HOVER BLOB AND MOVE MOUSE
		//THROUGH POP-UP DIV ONTO ANOTHER BLOB, LEAVING 2 BLOBS HOVERED.
		//WITH MANY BLOBS CLOSE TOGETHER YOU CAN BASICALLY HOVER OVER
		//ALL BLOBS AT THE SAME TIME!
		//
		//TO FIX THIS JUST REDRAW THE ORIGINAL HOVERED BLOB WHEN MOUSE
		//LEAVES POPUP
		drawBlob(hoveredBlob);
	})
	
}

/* - - - - - */

var c;

var highestScore;
var lowestDate;
var highestDate;
var dateRange;
