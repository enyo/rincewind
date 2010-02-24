YAHOO.SnapTest.TestLoader = function() {
	var onAllTestsLoadComplete = new YAHOO.util.CustomEvent("allTestsLoadComplete", this);
	var onTestLoadComplete = new YAHOO.util.CustomEvent("allTestsLoadComplete", this);
	var onRequestError = new YAHOO.util.CustomEvent("requestError", this);
	
	this.onAllTestsLoadComplete = onAllTestsLoadComplete;
	this.onTestLoadComplete = onTestLoadComplete;
	this.onRequestError = onRequestError;
	
	this.toString = function() { return "YAHOO.SnapTest.TestLoader"; };
	
	var files = [];
	
	var scope = this;

	var queue_size = 3;
	var queue = [];
	for (var i = 0; i < queue_size; i++) {
		queue.push({
			tid: false,
			proc: false
		});
	}
	
	var getTestsSuccess = function(o) {
		// remove the item from the queue
		for (var i = 0; i < queue_size; i++) {
			if (!queue[i].txn) {
				continue;
			}
			if (queue[i].txn.tId == o.tId) {
				queue[i] = {
					txn: false,
					proc: false
				};
			}
		}

		try {
		    var details = YAHOO.lang.JSON.parse(o.responseText);
		}
		catch (e) {
		    onRequestError.fire(e);
			return;
		}

		var tests = [];

		// add every test found onto the test stack
		var details_length = details.length;
		for (var i = 0; i < details_length; i++) {
			tests.push({
				file: details[i].file,
				klass: details[i].klass,
				test: details[i].test
			});
		}
		
		onTestLoadComplete.fire(tests);
	};
	
	var getTestsFailure = function(o) {
		// remove the item from the queue
		var result = {};
		var file = null;
		for (var i = 0; i < queue_size; i++) {
			if (!queue[i].txn) {
				continue;
			}
			if (queue[i].txn.tId == o.tId) {
				file = queue[i].proc;
				queue[i] = {
					txn: false,
					proc: false
				};
			}
		}
		
		onRequestError.fire(o);
		return;
	};
	
	this.addFile = function(file) {
		files.push(file);
	};
	
	this.getTests = function() {
		// look for an available slot
		for (var i = 0; i < queue_size; i++) {
			if (queue[i].txn) {
				continue;
			}
			
			if (files.length == 0) {
				continue;
			}
			
			var f = files.shift();
			
			// put a file in
			queue[i].txn = YAHOO.util.Connect.asyncRequest('POST', YAHOO.SnapTest.Constants.TEST_LOADER, {
				success: getTestsSuccess,
				failure: getTestsFailure
			}, "file="+f);
			queue[i].proc = f;
		}
		
		// fies left, go again
		if (files.length > 0) {
			window.setTimeout(scope.getTests, 10);
			return;
		}
		
		// if there's no queue, no files, we are done
		if (files.length == 0) {
			for (var i = 0; i < queue_size; i++) {
				if (queue[i].txn) {
					window.setTimeout(scope.getTests, 10);
					return;
				}
			}
			
			// done!
			onAllTestsLoadComplete.fire();
		}
	};
};