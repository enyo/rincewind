YAHOO.SnapTest.TestRunner = function() {
	var onTestStarted = new YAHOO.util.CustomEvent("testStarted", this);
	var onTestComplete = new YAHOO.util.CustomEvent("testComplete", this);
	var onAllTestsComplete = new YAHOO.util.CustomEvent("allTestsComplete", this);
	var onRequestError = new YAHOO.util.CustomEvent("requestError", this);
	
	this.onTestStarted = onTestStarted;
	this.onTestComplete = onTestComplete;
	this.onAllTestsComplete = onAllTestsComplete;
	this.onRequestError = onRequestError;
	
	this.toString = function() { return "YAHOO.SnapTest.TestRunner"; };
	
	var tests = [];
	
	var scope = this;

	var queue_size = 3;
	var queue = [];
	for (var i = 0; i < queue_size; i++) {
		queue.push({
			txn: false,
			proc: false
		});
	}
	
	var runTestsSuccess = function(o) {
		// remove the item from the queue
		var proc = null;
		
		// remove the item from the queue
		for (var i = 0; i < queue_size; i++) {
			if (!queue[i].txn) {
				continue;
			}
			if (queue[i].txn.tId == o.tId) {
				proc = queue[i].proc;
				queue[i] = {
					txn: false,
					proc: false
				};
			}
		}
		
		try {
		    var results = YAHOO.lang.JSON.parse(o.responseText);
		}
		catch (e) {
		    onRequestError.fire(proc.file, proc.klass, proc.test, o.responseText);
			return;
		}

		var tests = [];

		// add every test found onto the test stack
		var results_length = results.length;
		for (var i = 0; i < results_length; i++) {
			// skip types
			var result = results[i];
			if (result.type == "case") {
				continue;
			}
			
			onTestComplete.fire(proc, result);
		}
	};
	
	var runTestsFailure = function(o) {
		// remove the item from the queue
		for (var i = 0; i < queue_size; i++) {
			if (!queue[i].txn) {
				continue;
			}
			if (queue[i].txn.tId == o.tId) {
				proc = queue[i].proc;
				queue[i] = {
					txn: false,
					proc: false
				};
			}
		}
		
		onRequestError.fire(proc.file, proc.klass, proc.test, o.responseText);
	};
	
	this.addTest = function(file, klass, test) {
		tests.push({
			file: file,
			klass: klass,
			test: test
		});
	};
	
	this.runTests = function() {
		// copy queue idea
		for (var i = 0; i < queue_size; i++) {
			if (queue[i].txn) {
				continue;
			}
			
			if (tests.length == 0) {
				continue;
			}
			
			var t = tests.shift();
			
			onTestStarted.fire(t.file, t.klass, t.test);
			
			// put a file in
			queue[i].txn = YAHOO.util.Connect.asyncRequest('POST', YAHOO.SnapTest.Constants.TEST_RUNNER, {
				success: runTestsSuccess,
				failure: runTestsFailure
			}, "file="+escape(t.file)+"&klass="+escape(t.klass)+"&test="+escape(t.test));
			queue[i].proc = t;
		}
		
		// fies left, go again
		if (tests.length > 0) {
			window.setTimeout(scope.runTests, 10);
			return;
		}
		
		// if there's no queue, no tests, we are done
		if (tests.length == 0) {
			for (var i = 0; i < queue_size; i++) {
				if (queue[i].txn) {
					window.setTimeout(scope.runTests, 10);
					return;
				}
			}
			
			// done!
			onAllTestsComplete.fire();
		}
	};
		
		// var postData = "";
		// 
		// // make URL call
		// var txn = YAHOO.util.Connect.asyncRequest('POST', YAHOO.SnapTest.Constants.TEST_RUNNER, {
		// 	success: function(o) {
		// 		// inline oncomplete fire with the arguments
		// 		onTestComplete.fire({foo: 1, bar: 2, baz: 3, quux: 4});
		// 	},
		// 	failure: function(o) {
		// 		// fire the error object
		// 		onTestComplete.fire([
		// 			{
		// 				"type": "debug",
		// 				"message": "debug message here!",
		// 				"file": "test1.stest.php",
		// 				"function": "testIsAFoo",
		// 				"class": "Klass1"
		// 			},
		// 			{
		// 				"type": "pass",
		// 				"file": "test1.stest.php",
		// 				"function": "testIsAFoo",
		// 				"class": "Klass1"
		// 			}
		// 		]);
		// 	}
		// }, postData);
	// };
};