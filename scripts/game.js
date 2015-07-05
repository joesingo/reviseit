/* JOE'S VERY BASIC CANVAS GAME FRAMEWORK
 * --------------------------------------
 * 
 * Make a game by first creating a new instsance of the Game object.
 * 		var myGame = new Game();
 * You can give it width and height or go with default of 500px
 * 
 * Add key controls like so:
 * 		myGame.setControl("s","myButton",whatToDoWhenPressed);
 * For normal alpha-numeric keys just write them (as above) - see code for list of special keys.
 * You can see whether key is down or not like so:
 * 		if (myGame.keys.myButton.isDown) {
 * 			//whatevs...
 * 		}
 * 
 * Make things happen by adding objects like so:
 * 		var myObject = {
 * 			"update": function(dt) {
 * 				//whatever...
 * 			}
 * 			"draw": function() {
 * 				//blah...
 * 			}
 * 		myGame.addObject(myObject);
 * 
 * myObject.update and myObject.draw will now be called every frame (once the game is started)
 * You can remove objects with myGame.removeObject(myObject);
 * 
 * You can do things when the canvas is clicked:
 * 		myGame.onclick = function(x,y,button) {
 * 			console.log("Clicked with button " + button + " at (" + x + "," + y + ")");
 * 		}
 * 'button' is from js click event, so look that up somewhere else...
 * Very similar thing for mouse movement:
 * 		myGame.onmousemove = function(x,y) {
 * 			//oosh...
 * 		}
 * ...and onmousedown and onmouseup.

 * You can set myGame.update and myGame.draw to your own functions if you want to - the normal stuff
 * will still be done along with your stuff (remember that update takes 1 argument: delta time - time since last frame in seconds)
 * 
 * You can access canvas object with myGame.canvas and context with myGame.c.
 * You can see whether left mouse button is down with myGame.mouseIsDown
 * 
 * You can clear canvas with myGame.clear().
 * 
 * You can stop drawing and updating my doing:
 * 		myGame.freeze();
 * And start again with:
 * 		myGame.unfreeze();
 * 
 * You can make frames to easily draw onto part of the canvas.
 * 		var myFrame = new Frame(parent,x,y,width,height);
 * x and y are the coordinates of where the top left of the frame will be drawn onto the parent canvas.
 * parent can be a game or another frame, it is the object that has the canvas that is to be drawn onto.

 * To actually draw things to the frame:
 * 		myFrame.draw = function() {
 * 			this.c.fillRect(0,0,10,10);
 * 		};
 *
 * myFrame.draw() should draw  things onto the frame canvas, myFrame.display() draws the frame canvas onto the parent canvas.
 *
 * By defauly myFrame.draw() and myFrame.display() are not called every frame, but you can change this.
 * 		var myFrame = new Frame(x,y,width,height,keepDrawing,keepDisplaying);
 *
 * myFrame.canvas is the canvas element (same thing as myGame.canvas) and myFrame.c is the context.
 *
 * You can see if a point is inside a frame (to tell whether frame was clicked for example) like so:
 * 		if (myFrame.isInside(x,y)) {
 * 			//(x,y) is inside frame...
 * 		}
 *
 * You can do myFrame.clear() to clear the frame.
 * 
 * Start the game with myGame.run. By default canvas will be appended to body but you can pass myGame.run a DOM element if you want it
 * to be appended to another element. Remember that you cannot do this in the head as no elements will exist!
 */

var thisGame;

var then, now;

function Game(width,height) {
	
	thisGame = this;
	
	this.running = false;
		
	//CANVAS SETUP
	this.canvas = document.createElement("canvas");
	this.canvas.width = width || 500;
	this.canvas.height = height || 500;
	this.c = this.canvas.getContext("2d");
	
	this.keys = {}; //HOLDS KEYCODE AND CALLBACK FUNCTION FOR EACH KEY
	this.objects = []; //OBJECTS TO UPDATE AND DRAW
	this.frames = []; //ARRAY OF EACH FRAME TO DRAW
	
	this.mouseIsDown = false;
	
	//KEY PRESSES
	addEventListener("keydown",function(e) {
		if (thisGame.running) {
			for (var i in thisGame.keys) {
				if (thisGame.keys[i].keycode == e.keyCode) {					
					if (thisGame.keys[i].isDown == false) {
						thisGame.keys[i].isDown = true;
					}
					if (thisGame.keys[i].action) {
						thisGame.keys[i].action();
					}
				}
			}
		}
	});
	//KEY UN-PRESSES
	addEventListener("keyup",function(e) {
		for (var i in thisGame.keys) {
			if (thisGame.keys[i].keycode == e.keyCode) {
				thisGame.keys[i].isDown = false;
			}
		}
	});
	
	//LISTEN FOR CLICKS
	this.canvas.addEventListener("click",function(e) {
		var x = e.pageX - thisGame.canvas.offsetLeft
		var y = e.pageY - thisGame.canvas.offsetTop;
		
		//DO USER-DEFINED CLICK FUNCTION
		if (thisGame.onclick) thisGame.onclick(x,y,e.button);
	});
	
	//LISTEN FOR MOUSE MOVEMENT
	this.canvas.addEventListener("mousemove",function(e) {
		var x = e.pageX - thisGame.canvas.offsetLeft
		var y = e.pageY - thisGame.canvas.offsetTop;		
		if (thisGame.onmousemove) thisGame.onmousemove(x,y);
	});
	
	//MOUSE UP AND DOWN
	this.canvas.addEventListener("mousedown",function(e) {
		if (e.button == 0) thisGame.mouseIsDown = true;

		var x = e.pageX - thisGame.canvas.offsetLeft
		var y = e.pageY - thisGame.canvas.offsetTop;
		if (thisGame.onmousedown) thisGame.onmousedown(x,y);
	});
	this.canvas.addEventListener("mouseup",function(e) {
		if (e.button == 0) thisGame.mouseIsDown = false;

		var x = e.pageX - thisGame.canvas.offsetLeft
		var y = e.pageY - thisGame.canvas.offsetTop;
		if (thisGame.onmouseup) thisGame.onmouseup(x,y);
	});
	
	//---

	this.setControl = function(keyname,name,action) {
		this.keys[name] = {
			"isDown": false,
			"keycode": getKeycode(keyname),
			"action": action
		}
	}

	this.run = function(parentElement) {
		parentElement = parentElement || document.body;		
		parentElement.appendChild(this.canvas);
		this.running = true;
		console.log("Running...");
		
		then = Date.now();
		setInterval(main,1);
	}
	
	this.clear = function() {
		this.c.clearRect(0,0,this.canvas.width,this.canvas.height);
	}

	var update = function(dt) {
				
		//UPDATE OBJECTS ADDED BY USER
		for (var i=0;i<thisGame.objects.length;i++) {
			if (thisGame.objects[i].update) {
				thisGame.objects[i].update(dt);
			}
		}
		
		//DO USER-DEFINED UPDATE
		if (thisGame.update) thisGame.update(dt);
		
		draw();
	}

	var draw = function() {
		
		//DO USER-DEFINED DRAW
		if (thisGame.draw) thisGame.draw();
		
		//DRAW FRAMES
		for (var i=0;i<thisGame.frames.length;i++) {

			if (thisGame.frames[i].keepDrawing) {
				if (thisGame.frames[i].draw) thisGame.frames[i].draw();
			}
			if (thisGame.frames[i].keepDisplaying) thisGame.frames[i].display();
		}
		
		//DRAW OBJECTS ADDED BY UESR
		for (var i=0;i<thisGame.objects.length;i++) {
			if (thisGame.objects[i].draw) {
				thisGame.objects[i].draw();
			}
		}
			
	}

	var main = function() {
		now = Date.now();
		var dt = (now - then) / 1000
		then = now;
		
		if (thisGame.running) update(dt);
	}
	
	this.freeze = function() {
		this.running = false;
	}
	
	this.unfreeze = function() {
		this.running = true;
	}
	
	this.addObject = function(o) {
		this.objects.push(o);
	}
	
	this.removeObject = function(o) {
		this.objects.splice(
			this.objects.indexOf(o), 1
		);
	}	

}

function Frame(parent,x,y,width,height,keepDrawing,keepDisplaying) {
	this.x = x;
	this.y = y;
	
	this.canvas = document.createElement("canvas");
	this.canvas.width = width;
	this.canvas.height = height;
	this.c = this.canvas.getContext("2d");

	this.parent = parent;

	this.isInside = function(px,py) {
		return px > x && px < x + width && py > y && py < y + height;
	}
	
	this.display = function() {
		this.parent.c.drawImage(this.canvas,this.x,this.y);
	}

	this.clear = function() {
		this.c.clearRect(0,0,this.canvas.width,this.canvas.height);
	}

	this.keepDrawing = keepDrawing;
	this.keepDisplaying = keepDisplaying;

	thisGame.frames.push(this);
}

//---

var specialKeys = {};
specialKeys.left = 37;
specialKeys.up = 38;
specialKeys.right = 39;
specialKeys.down = 40;
specialKeys.space = 32;
specialKeys.esc = 27;
specialKeys.shift = 16;
specialKeys.ctrl = 17;
specialKeys.alt = 18;
specialKeys.back = 8;
specialKeys.tab = 9;
specialKeys.return = 13;

function getKeycode(name) {
	for (var i in specialKeys) {
		if (i == name) {
			return specialKeys[i];
		}
	}
	return name.toUpperCase().charCodeAt(0);
}
