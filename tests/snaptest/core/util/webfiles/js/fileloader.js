YAHOO.SnapTest.FileLoader = function() {
	var onFileLoadComplete = new YAHOO.util.CustomEvent("fileLoadComplete", this);
	var onRequestError = new YAHOO.util.CustomEvent("requestError", this);
	
	this.onFileLoadComplete = onFileLoadComplete;
	this.onRequestError = onRequestError;
	
	this.toString = function() { return "YAHOO.SnapTest.FileLoader"; };
	
	this.getFiles = function() {
		// make URL call
		var txn = YAHOO.util.Connect.asyncRequest('POST', YAHOO.SnapTest.Constants.FILE_LOADER, {
			success: function(o) {
				try {
				    var files = YAHOO.lang.JSON.parse(o.responseText);
				}
				catch (e) {
				    onRequestError.fire(e);
					return;
				}
				
				// inline oncomplete fire with the arguments
				onFileLoadComplete.fire(files);
			},
			failure: function(o) {
				onRequestError.fire(o);
			}
		}, null);
	};
};