YAHOO.SnapTest.Manager = (function() {
	var FL = new YAHOO.SnapTest.FileLoader();
	var TL = new YAHOO.SnapTest.TestLoader();
	var TR = new YAHOO.SnapTest.TestRunner();
	var Display = YAHOO.SnapTest.DisplayManager;
	
	var Logger = new YAHOO.widget.LogWriter("Manager"); 
	
	var runTests = function() {
		Display.disableTestingButton();
		
		// hide all our tests we aren't testing
		Display.hideUncheckedTests();
		
		var boxes = Display.getTestList();
		var boxes_length = boxes.length;

		for (var i = 0; i < boxes_length; i++) {
			var pieces = boxes[i].split('|||');
			var file = pieces[0];
			var klass = pieces[1];
			var test = pieces[2];
			
			TR.addTest(file, klass, test);
		}

		Logger.log("Dispatching "+TR.toString()+".runTests()");
		TR.runTests();
	};
	
	FL.onFileLoadComplete.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		var results = args[0];
		var results_length = results.length;
		for (var i = 0; i < results_length; i++) {
			TL.addFile(results[i]);
			Display.addFile(results[i]);
		}
		Display.showMessage("Getting tests");
		
		Logger.log("Dispatching "+TL.toString()+".getTests()");
		TL.getTests();
	});
	
	TL.onTestLoadComplete.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		var results = args[0];
		var results_length = results.length;
		
		for (i = 0; i < results_length; i++) {
			Display.showMessage("Popularing tests in "+results[i].file);
			Display.addTestToFile(results[i].file, results[i].klass, results[i].test);
		}
	});
	
	TL.onAllTestsLoadComplete.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		Display.showMessage("Test loading complete. Ready to run.");
		
		// autorun flag
		if (location.search.match(/autorun=true/)) {
			runTests();
			return;
		}
		
		Display.enableTestingButton();
	});
	
	TR.onTestComplete.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		// 0 is results
		Display.recordTestResults(args[0], args[1]);
	});
	
	TR.onTestStarted.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		var file = args[0];
		var klass = args[1];
		var test = args[2];
		Display.showMessage("Testing "+klass+"::"+test);
	});
	
	TR.onRequestError.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		var proc = {
			file: args[0],
			klass: args[1],
			test: args[2]
		};
		
		var results = {
			type: "defect",
			message: args[3]
		};
		
		Display.recordTestResults(proc, results);
	});
	
	TR.onAllTestsComplete.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		Display.enableResultsPaging();
		Display.showTestResults();
		Display.returnToTopOfTestList();
	});
	
	Display.onRunTests.subscribe(function(type, args, caller) {
		Logger.log("Caught event of type "+type);
		
		runTests();
	});
	

	var iface = {
		init: function() {
			Display.init();
			Display.disableTestingButton();
			Display.disableResultsPaging();
			Display.showMessage("Getting files");
			
			Logger.log("Dispatching "+FL.toString()+".getFiles()");
			FL.getFiles();
		},
		reset: function() {
			Display.clear();
		}
	};
	
	return iface;
})();

YAHOO.util.Event.onDOMReady(function() {
	// get a logger set up if enabled
	if (location.search.match(/debug=true/)) {
		var logdiv = document.createElement("div");
		logdiv.id = "logger";
		var myLogReader = new YAHOO.widget.LogReader(logdiv, { newestOnTop: true });
		document.body.insertBefore(logdiv, document.body.firstChild);
	}
	
	YAHOO.SnapTest.Manager.init();
});
