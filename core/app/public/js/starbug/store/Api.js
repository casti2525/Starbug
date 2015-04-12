define([
	"dojo/io-query", "dojo/_base/declare", "dojo/_base/lang", "dojo/store/util/QueryResults", "starbug/form/Dialog", "dojo/request/registry", "dojo/cookie"
], function(ioQuery, declare, lang, QueryResults, dialog, request, cookie) {
return declare(null, {
	// query: String
	//		The API query string
	apiQuery: "",
	// idProperty: String
	//		Indicates the property to use as the identity property. The values of this
	//		property should be unique.
	model: '',
	action:'admin',
	post_action: 'create',
	idProperty: "id",
	last_query:'',
	results:null,
	params:{},
	constructor: function(/*starbug.store.Api*/ options){
		// summary:
		//		This is a basic store for RESTful communicating with a server through JSON
		//		formatted data.
		// options:
		//		This provides any configuration information that will be mixed into the store
		lang.mixin(this, options);
	},
	get: function(id, options){
		//	summary:
		//		Retrieves an object by its identity. This will trigger a GET request to the server using
		//		the url `this.target + id`.
		//	id: Number
		//		The identity to use to lookup the object
		//	returns: Object
		//		The object in the store that matches the given id.
		var args = sb.star(this.apiQuery);
		var parts = [];
		args.id = id;
		var q = ioQuery.objectToQuery(args);
		q = q ? "?" + q: "";
		var headers = options || {};
		headers.Accept = "application/javascript, application/json";
		this.results = request.get(WEBSITE_URL+'api/'+this.model+'/'+this.action+'.json'+(q || ''), {
			handleAs: "json",
			headers: headers
		});
		this.results.then(function() {}, lang.hitch(this, 'handleError'));
		return this.results;
	},
	getIdentity: function(object){
		// summary:
		//		Returns an object's identity
		// object: Object
		//		The object to get the identity from
		//	returns: Number
		return object[this.idProperty];
	},
	put: function(object, options){
		// summary:
		//		Stores an object. This will trigger a PUT request to the server
		//		if the object has an id, otherwise it will trigger a POST request.
		// object: Object
		//		The object to store.
		// options: dojo.store.api.Store.PutDirectives?
		//		Additional metadata for storing the data.
		//	returns: Number
		var data = {};
		for (var k in object) data[this.model+'['+k+']'] = object[k];
		options = options || {};
		data['action['+this.model+']'] = this.post_action;
		data.oid = cookie('oid');
		this.results = request.post(WEBSITE_URL+'api/'+this.model+'/'+this.action+'.json', {data: data, handleAs: "json"});
		this.results.then(function(){}, lang.hitch(this, 'handleError'));
		return this.results;
	},
	add: function(object, options){
		// summary:
		//		Adds an object. This will trigger a PUT request to the server
		//		if the object has an id, otherwise it will trigger a POST request.
		// object: Object
		//		The object to store.
		// options: dojo.store.api.Store.PutDirectives?
		//		Additional metadata for storing the data.  Includes an "id"
		//		property if a specific id is to be used.
		options = options || {};
		options.overwrite = false;
		return this.put(object, options);
	},
	remove: function(id){
		// summary:
		//		Deletes an object by its identity. This will trigger a DELETE request to the server.
		// id: Number
		//		The identity to use to delete the object
		var args = {};
		args['action['+this.model+']'] = 'delete';
		args[this.model+'[id]'] = id;
		args['oid'] = cookie('oid');
		this.results = request.post(WEBSITE_URL+'api/'+this.model+'/get.json', {data: args});
		this.results.then(function(){}, lang.hitch(this, 'handleError'));
		return this.results;
	},
	query: function(query, options){
		// summary:
		//		Queries the store for objects. This will trigger a GET request to the server, with the
		//		query added as a query string.
		// query: Object
		//		The query to use for retrieving objects from the store.
		// options: dojo.store.api.Store.QueryOptions?
		//		The optional arguments to apply to the resultset.
		//	returns: dojo.store.api.Store.QueryResults
		//		The results of the query, extended with iterative methods.
		var headers = {Accept: "application/javascript, application/json"};
		query = query || {};
		for (x in this.params) if (typeof query[x] == 'undefined') query[x] = this.params[x];
		options = options || {};
		if(options.start >= 0 || options.count >= 0){
			headers.Range = "items=" + (options.start || '0') + '-' +
				(("count" in options && options.count != Infinity) ?
					(options.count + (options.start || 0) - 1) : '');
		}
		query = ioQuery.objectToQuery(query);
		query = query ? "?" + query: "";
		if(options && options.sort){
			query += (query ? "&" : "?") + "orderby=";
			for(var i = 0; i<options.sort.length; i++){
				var sort = options.sort[i];
				query += (i > 0 ? "," : "") + encodeURIComponent(sort.attribute+' '+(sort.descending ? 'DESC' : 'ASC'));
			}
		}
		var query_url = WEBSITE_URL+'api/'+this.model+'/'+this.action+'.json' + (query || "");
		this.last_query = query_url;
		var results = request.get(query_url, {handleAs: "json", headers: headers});
		results.then(function(){}, lang.hitch(this, 'handleError'));
		results.total = results.response.then(function(response){
			var range = response.getHeader("Content-Range");
			return range && (range=range.match(/\/(.*)/)) && +range[1];
		});
		this.results = QueryResults(results);
		return this.results;
	},
	mayHaveChildren: function(object) {
		if (typeof object['children'] != "undefined" && object['children'] == 0) return false;
		return true;
	},
	getChildren: function(object, options){
		var old_result = this.results;
		this.query(lang.mixin({}, options && options.originalQuery || null, {parent:object.id}), options);
		return this.results;
		this.results = old_result;
	},
	handleError: function(error) {
		var self = this;
		if (error.response.status == 403) {
			var d = new dialog({url:WEBSITE_URL+'forbidden', crudSuffixes:false, callback:function() {
				/*
					var method = (typeof ioargs.args['content'] == "undefined") ? dojo.xhrGet : dojo.xhrPost;
					method(ioargs.args).then(function(results) {
						self.results.resolve(results);
					});
				*/
			}});
			d.show();
		} else if (error.response.status == 500 && error.response.text.substr(0, 1) == '{') {
			var data = JSON.parse(error.response.text);
			var message = 'Message: '+data.message+'\n\nFile: '+data.file+'\n\nLine: '+data.line;
			alert(message);
		}	else if (error.response.status > 0) {
			alert('An unknown error occurred.');
		}
	}
});
});
