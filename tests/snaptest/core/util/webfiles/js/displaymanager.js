YAHOO.SnapTest.DisplayManager = (function() {
	var onRunTests = new YAHOO.util.CustomEvent("runTests", this);
	
	var last_scroll_y = 0;
	
	var help_panel = null;
	
	var test_tally = {
		pass: 0,
		defect: 0,
		fail: 0,
		skip: 0,
		todo: 0
	};
	
	var id_mapping = {};
	
	var Logger = new YAHOO.widget.LogWriter("DisplayManager");
	
	var getHeirarchy = function(file, klass, test, suffix) {
		id = [];
		
		if (file) {
			id.push(getId(file));
		}
		if (klass) {
			id.push(getId(file, klass));
		}
		if (test) {
			id.push(getId(file, klass, test));
		}
		if (suffix) {
			id.push(getId(file, klass, test, suffix));
		}
		
		return id.join("_");
	};
	
	var getId = function(file, klass, test, suffix) {
		
		if (!klass) {
			klass = '';
		}
		if (!test) {
			test = '';
		}
		if (!suffix) {
			suffix = '';
		}
		
		var name = file.replace(/\//g, '_').replace(/\./g, '__')+"_"+klass+"_"+test+"_"+suffix;
		
		if (!id_mapping[name]) {
			id_mapping[name] = YAHOO.util.Dom.generateId();
		}
		
		return id_mapping[name];
	};
	
	var makeCheckbox = function(file, klass, test) {
		// IE requires a checkbox to be made differently. Boo.
		try {
			var cb = document.createElement("<input type=\"checkbox\" checked>");
		}
		catch (e) {
			var cb = document.createElement("input");
			cb.type = "checkbox";
			cb.checked = true;
		}
		
		Logger.log("Making checkbox for file: "+file+" klass: "+klass+" test: "+test);
		
		cb.id = YAHOO.util.Dom.generateId();
		
		cb.value = file+"|||"+klass+"|||"+test;
		
		if (file) {
			YAHOO.util.Dom.addClass(cb, getHeirarchy(file));
			
			if (!klass && !test) {
				YAHOO.util.Event.addListener(cb, 'click', function(e) {
					var nodes = YAHOO.util.Dom.getElementsByClassName(getHeirarchy(file));
					var nodes_length = nodes.length;
					for (var i = 0; i < nodes_length; i++) {
						var node = nodes[i];
						node.checked = cb.checked;
					}
				});
			}
		}
		if (klass) {
			YAHOO.util.Dom.addClass(cb, getHeirarchy(file, klass));
			
			if (!test) {
				YAHOO.util.Event.addListener(cb, 'click', function(e) {
					var nodes = YAHOO.util.Dom.getElementsByClassName(getHeirarchy(file, klass));
					var nodes_length = nodes.length;
					for (var i = 0; i < nodes_length; i++) {
						var node = nodes[i];
						node.checked = cb.checked;
					}
				});
			}
		}
		if (test) {
			YAHOO.util.Dom.addClass(cb, "test_selector");
			YAHOO.util.Dom.addClass(cb, getHeirarchy(file, klass, test));
		}
		
		// add a special class for file-only level things
		if (file && !klass && !test) {
			YAHOO.util.Dom.addClass(cb, "file_selector");
		}
		
		return cb;
	};
	
	var makeFoldingControl = function() {
		var div = document.createElement("div");
		var div_id = YAHOO.util.Dom.generateId();
		div.id = div_id;
		
		var a = document.createElement("a");
		a.href="";
		
		YAHOO.util.Dom.addClass(div, "folder");
		
		var txt = document.createTextNode("-");
		
		YAHOO.util.Event.addListener(a, "click", function(e) {
			YAHOO.util.Event.stopEvent(e);
			
			var p = YAHOO.util.Dom.get(div_id).parentNode;
			
			while (a.firstChild) {
				a.removeChild(a.firstChild);
			}
			
			if (YAHOO.util.Dom.hasClass(p, "folded")) {
				YAHOO.util.Dom.removeClass(p, "folded");
				a.appendChild(document.createTextNode("-"));
			}
			else {
				YAHOO.util.Dom.addClass(p, "folded");
				a.appendChild(document.createTextNode("+"));
			}
		});
		
		div.appendChild(a);
			a.appendChild(txt);
		
		return div;
	};
	
	var attachCorners = function(div, types) {
		
		if (!types) {
			types = {
				top: true,
				left: true,
				bottom: true,
				right: true
			};
		}
		
		if (types.top && types.left) {
			var tl = document.createElement("div");
			YAHOO.util.Dom.addClass(tl, "rc_tl");
			div.appendChild(tl);
		}
		
		if (types.top && types.right) {
			var tr = document.createElement("div");
			YAHOO.util.Dom.addClass(tr, "rc_tr");
			div.appendChild(tr);
		}
		
		if (types.bottom && types.left) {
			var bl = document.createElement("div");
			YAHOO.util.Dom.addClass(bl, "rc_bl");
			div.appendChild(bl);
		}
			
		if (types.bottom && types.right) {
			var br = document.createElement("div");
			YAHOO.util.Dom.addClass(br, "rc_br");
			div.appendChild(br);
		}
	};
	
	var clear = function() {
		Logger.log("Cleared");
		
		var test_container = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_CONTAINER);
		while (test_container.firstChild) {
			test_container.removeChild(test_container.firstChild);
		}
		
		var ul = document.createElement("ul");
		ul.id = YAHOO.SnapTest.Constants.TEST_LIST;
		
		YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_CONTAINER).appendChild(ul);
	};
	
	var init = function() {
		YAHOO.SnapTest.DisplayManager.clear();
		window.scrollTo(0,0);
	};
	
	var addFile = function(file) {
		Logger.log("Adding file "+file);
		
		var li = document.createElement("li");
		li.id = getHeirarchy(file);
		YAHOO.util.Dom.addClass(li, "file_group");
		
		var cb = makeCheckbox(file);
		
		var label = document.createElement("label");
		label.setAttribute("for", cb.id);
		
		var p = document.createElement("p");
		YAHOO.util.Dom.addClass(p, "file_name");
		
		var fileDisplay = file;
        // fileDisplay = fileDisplay.substr(fileDisplay.length - 32, 32);
		var txt = document.createTextNode(fileDisplay);
		
		YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.TEST_LIST).appendChild(li);
			li.appendChild(makeFoldingControl());
			li.appendChild(label);
				label.appendChild(cb);
				label.appendChild(p);
					p.appendChild(txt);
	};
	
	var addTestToFile = function(file, klass, test) {
		Logger.log("Adding test "+klass+"::"+test+" to file "+file);
		
		var file_container = YAHOO.util.Dom.get(getHeirarchy(file));
		
		// alert('adding '+file+'::'+klass+'::'+test);
		
		if (!YAHOO.util.Dom.get(getHeirarchy(file, klass))) {
			var div = document.createElement("div");
			div.id = getHeirarchy(file, klass, null, '_GROUP');
			YAHOO.util.Dom.addClass(div, "test_group");
			
			var ul = document.createElement("ul");
			
			var li = document.createElement("li");
			
			var cb = makeCheckbox(file, klass);
			
			var label = document.createElement("label");
			label.setAttribute("for", cb.id);
			
			YAHOO.util.Dom.addClass(cb, "test_group_box");
			
			var p = document.createElement("p");
			
			var txt = document.createTextNode(klass);
			
			var dl = document.createElement("dl");
			dl.id = getHeirarchy(file, klass);
			
			file_container.appendChild(div);
				div.appendChild(ul);
					ul.appendChild(li);
						li.appendChild(makeFoldingControl());
						li.appendChild(label);
							label.appendChild(cb);
							label.appendChild(p);
								p.appendChild(txt);
						li.appendChild(dl);
				attachCorners(div);
		}
		
		// now we can add the test
		var klass_container = YAHOO.util.Dom.get(getHeirarchy(file, klass));
		
		var dt = document.createElement("dt");
		dt.id = getHeirarchy(file, klass, test);
		// YAHOO.util.Dom.addClass(dt, testToId(file, klass, test));
		YAHOO.util.Dom.addClass(dt, "test");
		
		var cb = makeCheckbox(file, klass, test);
		
		var label = document.createElement("label");
		label.setAttribute("for", cb.id);
		
		var txt = document.createTextNode(test);
		
		var dd = document.createElement("dd");
		dd.id = getHeirarchy(file, klass, test, '_RESULTS');
		YAHOO.util.Dom.addClass(dd, "result_container");
		
		klass_container.appendChild(dt);
			dt.appendChild(label);
				label.appendChild(cb);
				label.appendChild(txt);
			attachCorners(dt);
		klass_container.appendChild(dd);
	};

	var recordTestResults = function(proc, results) {
		var file = proc.file;
		var klass = proc.klass;
		var test = proc.test;
		
		Logger.log("Recording test results for "+klass+"::"+test+" in "+file);
		
		var test_container = getHeirarchy(file, klass, test);
		var result_container = getHeirarchy(file, klass, test, '_RESULTS');
		var result_node = YAHOO.util.Dom.get(result_container);
		
		YAHOO.util.Dom.addClass(test_container, results.type);
		YAHOO.util.Dom.addClass(test_container, "complete");
		YAHOO.util.Dom.addClass(result_container, results.type);
		
		while (result_node.firstChild) {
			result_node.removeChild(result_node.firstChild);
		}
		
		checkTests(YAHOO.util.Dom.get(getHeirarchy(file, klass)));
		checkTests(YAHOO.util.Dom.get(getHeirarchy(file, klass, null, '_GROUP')));
		
		test_tally[results.type]++;
		
		// pass are skipped
		if (results.type == "pass") {
			return;
		}
		
		// everything else is logged
		var p = document.createElement("p");
		
		// if the server hands back HTML formatted errors, use innerHTML instead
		if (results.message.match(/</)) {
			p.innerHTML = results.message;
		}
		else {
			var txt = document.createTextNode(results.message);
			p.appendChild(txt);
		}

		var dl = document.createElement("dl");
		
		YAHOO.util.Dom.addClass(dl, "details");
		
		var dt_test = document.createElement("dt");
		var dt_test_txt = document.createTextNode("in method:");
		var dd_test = document.createElement("dd");
		var dd_test_txt = document.createTextNode(test);
		
		var dt_klass = document.createElement("dt");
		var dt_klass_txt = document.createTextNode("in class:");
		var dd_klass = document.createElement("dd");
		var dd_klass_txt = document.createTextNode(klass);
		
		var dt_file = document.createElement("dt");
		var dt_file_txt = document.createTextNode("in file:");
		var dd_file = document.createElement("dd");
		var dd_file_txt = document.createTextNode(file);
		
		var div = document.createElement("div");
		YAHOO.util.Dom.addClass(div, "clear");

		result_node.appendChild(p);
		result_node.appendChild(dl);
			dl.appendChild(dt_test);
				dt_test.appendChild(dt_test_txt);
			dl.appendChild(dd_test);
				dd_test.appendChild(dd_test_txt);
			dl.appendChild(dt_klass);
				dt_klass.appendChild(dt_klass_txt);
			dl.appendChild(dd_klass);
				dd_klass.appendChild(dd_klass_txt);
			dl.appendChild(dt_file);
				dt_file.appendChild(dt_file_txt);
			dl.appendChild(dd_file);
				dd_file.appendChild(dd_file_txt);
		result_node.appendChild(div);
		attachCorners(result_node);
	};
	
	var showTestResults = function() {
		var pass = test_tally.pass;
		var fail = test_tally.fail;
		var defect = test_tally.defect;
		var skip = test_tally.skip;
		var todo = test_tally.todo;
		
		var testcount = pass + fail + defect + skip + todo;
		
		var msg = "Tests: "+testcount+", Pass: "+pass+", Fail: "+fail+", Defect: "+defect+", Skip: "+skip+", Todo: "+todo;
		
		showMessage(msg);
	};
	
	var checkTests = function(node) {
		// get all tests under that
		var nodes = YAHOO.util.Dom.getElementsByClassName("test", null, node);
		var nodes_length = nodes.length;
		
		var pass = true;
		var fail = false;
		var complete = true;
		for (var i = 0; i < nodes_length; i++) {
			if (!YAHOO.util.Dom.hasClass(nodes[i], "complete")) {
				pass = false;
				complete = false;
				break;
			}
			if (!YAHOO.util.Dom.hasClass(nodes[i], "pass")) {
				pass = false;
				fail = true;
				break;
			}
		}

		if (complete) {
			YAHOO.util.Dom.addClass(node, "complete");
			
			// scroll to it if it's farther down our page
			var scroll_to = YAHOO.util.Dom.getY(node);
			if (scroll_to > last_scroll_y) {
				window.scrollTo(0, scroll_to);
			}
		}		
		if (pass) {
			YAHOO.util.Dom.addClass(node, "pass");
		}
		if (fail) {
			YAHOO.util.Dom.addClass(node, "warning");
		}
	};
	
	var returnToTopOfTestList = function() {
		window.scrollTo(0,0);
	};
	
	var showMessage = function(msg) {
		Logger.log("Showing message: "+msg);
		
		var node = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.MESSAGE_CONTAINER);
		while (node.firstChild) {
			node.removeChild(node.firstChild);
		}
		
		node.appendChild(document.createTextNode(msg));
	};
	
	var getTestList = function() {
		var tests = [];
		
		var nodes = YAHOO.util.Dom.getElementsByClassName("test_selector");
		var nodes_length = nodes.length;
		for (var i = 0; i < nodes_length; i++) {
			if (nodes[i].checked) {
				tests.push(nodes[i].value);
			}
		}
		
		Logger.log("Geting tests, found "+tests.length+" tests");
		
		return tests;
	};
	
	var hideUncheckedTests = function() {
		Logger.log("Hiding unchecked tests");
		
		var tests = [];
		
		var nodes = YAHOO.util.Dom.getElementsByClassName("test_selector");
		var nodes_length = nodes.length;
		for (var i = 0; i < nodes_length; i++) {
			if (!nodes[i].checked) {
				var pieces = nodes[i].value.split("|||");
				var file = pieces[0];
				var klass = pieces[1];
				var test = pieces[2];
				
				YAHOO.util.Dom.setStyle(nodes[i].parentNode.parentNode, "display", "none");
			}
		}
		
		return tests;
	};
	
	var error_scroll = null;
	var scrollToError = function(by) {
		var nodes = [];
		
		var list = YAHOO.util.Dom.getElementsByClassName('fail', 'dt');
		var list_length = list.length;
		for (var i = 0; i < list_length; i++) {
			nodes.push(list[i]);
		}
		var list = YAHOO.util.Dom.getElementsByClassName('defect', 'dt');
		var list_length = list.length;
		for (var i = 0; i < list_length; i++) {
			nodes.push(list[i]);
		}
		
		var nodes_length = nodes.length;
		
		if (error_scroll !== null) {
			var next_node = error_scroll + by;
		
			if (next_node < 0) {
				return;
			}
		
			if (next_node > nodes_length) {
				next_node = 0;
			}
		}
		else {
			next_node = 0;
		}
		
		error_scroll = next_node;
		
		window.scrollTo(0, YAHOO.util.Dom.getY(nodes[next_node]));
	};
	
	var disableTestingButton = function() {
		var btn = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON);
		
		YAHOO.util.Event.removeListener(btn, "click");
		
		YAHOO.util.Dom.removeClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_run_tests");
	};
	
	var enableTestingButton = function() {
		var btn = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.RUN_TESTS_BUTTON);
		
		YAHOO.util.Event.addListener(btn, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			onRunTests.fire();
		});
		
		YAHOO.util.Dom.addClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_run_tests");
	};
	
	var disableResultsPaging = function() {
		var btn_next = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.NEXT_ERROR_BUTTON);
		var btn_prev = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.PREV_ERROR_BUTTON);
		
		YAHOO.util.Event.removeListener(btn_next, "click");
		YAHOO.util.Event.removeListener(btn_prev, "click");
		
		YAHOO.util.Dom.removeClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_review_tests");
	};
	
	var enableResultsPaging = function() {
		var btn_next = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.NEXT_ERROR_BUTTON);
		var btn_prev = YAHOO.util.Dom.get(YAHOO.SnapTest.Constants.PREV_ERROR_BUTTON);
		
		YAHOO.util.Event.addListener(btn_next, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			scrollToError(1);
		});
		
		YAHOO.util.Event.addListener(btn_prev, 'click', function(e) {
			YAHOO.util.Event.stopEvent(e);
			scrollToError(-1);
		});
		
		YAHOO.util.Dom.addClass(YAHOO.SnapTest.Constants.APP_CONTROLS, "status_review_tests");
	};
	
	// help system
	YAHOO.util.Event.onDOMReady(function() {

		YAHOO.util.Event.addListener("help", "click", function(e) {
			YAHOO.util.Event.stopEvent(e);
			
			if (!help_panel) {
				help_panel = new YAHOO.widget.Panel("help_popup",  {
					width: "500px", 
			  		fixedcenter: true, 
			  		close: true, 
			  		draggable: false, 
					underlay: "shadow",
			  		zindex: 20,
			  		modal: true,
			  		visible: false
				});
			}
			
			help_panel.setHeader("SnapTest Web Console Help");
			help_panel.setBody(YAHOO.util.Dom.get("help_contents").innerHTML);
			
			help_panel.render("snaptest");
			help_panel.show();
		});

		YAHOO.util.Event.addListener("collapse_all", "click", function(e) {
			YAHOO.util.Event.stopEvent(e);
			
			var nodes = YAHOO.util.Dom.getElementsByClassName("file_group", "li");
			var nodes_length = nodes.length;
			for (var i = 0; i < nodes_length; i++) {
				if (!YAHOO.util.Dom.hasClass(nodes[i], "folded")) {
					YAHOO.util.Dom.addClass(nodes[i], "folded");
				}
			}
		});
		
		YAHOO.util.Event.addListener("expand_all", "click", function(e) {
			YAHOO.util.Event.stopEvent(e);
			
			var nodes = YAHOO.util.Dom.getElementsByClassName("file_group", "li");
			var nodes_length = nodes.length;
			for (var i = 0; i < nodes_length; i++) {
				if (YAHOO.util.Dom.hasClass(nodes[i], "folded")) {
					YAHOO.util.Dom.removeClass(nodes[i], "folded");
				}
			}
		});

	});
	
	var iface = {};
	iface.toString = function() { return "YAHOO.SnapTest.DisplayManager"; };
	
	// methods
	iface.clear = clear;
	iface.init = init;
	iface.addFile = addFile;
	iface.addTestToFile = addTestToFile;
	iface.recordTestResults = recordTestResults;
	iface.getTestList = getTestList;
	iface.hideUncheckedTests = hideUncheckedTests;
	iface.showMessage = showMessage;
	
	iface.showTestResults = showTestResults;
	
	iface.disableTestingButton = disableTestingButton;
	iface.enableTestingButton = enableTestingButton;
	iface.enableResultsPaging = enableResultsPaging;
	iface.disableResultsPaging = disableResultsPaging;
	
	iface.returnToTopOfTestList = returnToTopOfTestList;
	
	// events
	iface.onRunTests = onRunTests;
	return iface;
})();