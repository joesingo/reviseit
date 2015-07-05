var validate = function(settings) {

	success = true;

	if ("presence" in settings) {
		$.each(settings.presence,function(){

			//REMOVE SPACES AT START OF FIELD
			var val = $(this).val().trim();

			if (val == "") testFailed(this);
			else testPassed(this);

		});
	}

	if ("email" in settings) {
		$.each(settings.email,function(){

			var re = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/;
			var val = $(this).val();

			if (!re.test(val)) testFailed(this);
			else testPassed(this);
		});
	}

	if ("match" in settings) {
		var workingValue = null;

		$.each(settings.match,function(){

			if (workingValue === null) {
				workingValue = $(this).val();
			}

			else {
				if ($(this).val() != workingValue) testFailed(this);
				else testPassed(this);
			}
		});
	}

	return success;
}

var testPassed = function(el) {
	$(el).removeClass("error");
	$("label[for=" + $(el).attr("id") + "]").removeClass("error");
}

var testFailed = function(el) {
	$(el).addClass("error");
	$("label[for=" + $(el).attr("id") + "]").addClass("error");

	success = false;
}