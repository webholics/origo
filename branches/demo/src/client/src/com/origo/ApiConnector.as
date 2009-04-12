package com.origo
{
	import com.adobe.crypto.MD5;
	
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.events.IOErrorEvent;
	import flash.events.SecurityErrorEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;

	/**
	 * ApiConnector
	 * 
	 * Use this class to communicate with the Origo REST API.
	 * The class is also used as data model for already received data.
	 */
	[Event(name="successEvent", type="ApiConnectorEvent")]
	[Event(name="errorEvent", type="ApiConnectorEvent")]
	public class ApiConnector extends EventDispatcher
	{
		/**
		 * Singleton instance
		 */
		private static var instance:ApiConnector = new ApiConnector();
        
        /**
         * Api url constants
         */
        private static const API_TEST:String 							= "test";
        private static const API_EDITOR_GET:String 						= "editor/get";
        private static const API_EDITOR_UPDATE:String 					= "editor/update";
        private static const API_EDITOR_DELETE:String 					= "editor/delete";
        private static const API_EDITOR_CLEAN:String 					= "editor/clean";
        private static const API_EDITOR_PROFILES_GET:String 			= "editor/profiles/get";
        private static const API_EDITOR_PROFILES_UPDATE:String 			= "editor/profiles/update";
        private static const API_EDITOR_PROFILES_DELETE:String 			= "editor/profiles/delete";
        private static const API_EDITOR_RELATIONSHIPS_GET:String 		= "editor/relationships/get";
        private static const API_EDITOR_RELATIONSHIPS_UPDATE:String 	= "editor/relationships/update";
        private static const API_EDITOR_RELATIONSHIPS_DELETE:String 	= "editor/relationships/delete";
        private static const API_BROWSER_PROFILE:String 				= "browser/profile";
        private static const API_BROWSER_RELATIONSHIPS:String 			= "browser/relationships";
        private static const API_BROWSER_CLEAN:String 					= "browser/clean";
		
		/**
		 * The base url of the REST-API.
		 */
		private var apiUrl:String = "";
		
		/**
		 * Has user authentication?
		 */
		private var _hasAuth:Boolean = false;
		
		/**
		 * Authorization hash to send via key param
		 */
		private var authHash:String = null;
		
		/**
		 * The url loader.
		 */
		private var urlLoader:URLLoader;

		public function ApiConnector()
		{
			if(instance) 
				throw new Error("ApiConnector can only be accessed through ApiConnector.getInstance()");
				
			urlLoader = new URLLoader();
			urlLoader.addEventListener(Event.COMPLETE, resultHandler);
			urlLoader.addEventListener(IOErrorEvent.IO_ERROR, errorHandler);
			urlLoader.addEventListener(SecurityErrorEvent.SECURITY_ERROR, errorHandler);
		}
		
		public static function getInstance():ApiConnector 
		{
			return instance;
		}
		
		/**
		 * Check if user has authentication.
		 */
		public function hasAuth():Boolean
		{
			return _hasAuth;
		}
		
		/**
		 * Set the api base url.
		 * 
		 * @param String apiUrl
		 */
		public function setApiUrl(apiUrl:String):void
		{
			this.apiUrl = apiUrl;
		}
		
		/**
		 * Try to authenticate the user.
		 * 
		 * @param String username
		 * @param String password
		 */
		public function authenticate(username:String, password:String):void
		{
			// construct auth hash
			authHash = MD5.hash(username + ":" + password);
			
			_hasAuth = false;
			
			// check credentials via test method provided by the api
			sendRequest(API_TEST);
		}
		
		/**
		 * Get properties of the user.
		 */
		public function editorGet():void
		{
			sendRequest(API_EDITOR_GET);
		}	
		
		/**
		 * Update properties of the user.
		 * 
		 * @param Array properties An assoziative array with key(string)-value(string) pairs.
		 */
		public function editorUpdate(properties:Array):void
		{
			var variables:URLVariables = new URLVariables();
			for(var key:String in properties) {
				variables[key] = properties[key];
			}
			
			sendRequest(API_EDITOR_UPDATE, variables);
		}
		
		/**
		 * Delete properties of the user.
		 * 
		 * @param Array properties An array with the properties to delete as string values.
		 */
		public function editorDelete(properties:Array):void
		{
			var variables:URLVariables = new URLVariables();
			for each(var p:String in properties) {
				variables[p] = "";
			}
			
			sendRequest(API_EDITOR_DELETE, variables);
		}
			
		/**
		 * Get relationships of the user.
		 */
		public function editorRelationshipsGet():void
		{
			sendRequest(API_EDITOR_RELATIONSHIPS_GET);
		}	
			
		/**
		 * Update a relationship of the user.
		 * 
		 * @param Array types An array with the relationship types as values.
		 */
		public function editorRelationshipsUpdate(uri:String, types:Array):void
		{
			var variables:URLVariables = new URLVariables();
			variables.to = uri;
			for each(var type:String in types) {
				variables[type] = "";
			}
			
			sendRequest(API_EDITOR_RELATIONSHIPS_UPDATE, variables);
		}
		
		/**
		 * Delete a relationship of the user.
		 * 
		 * @param String uri The personal URI of the relationship to delete.
		 */
		public function editorRelationshipsDelete(uri:String):void
		{
			var variables:URLVariables = new URLVariables();
			variables.to = uri;
			
			sendRequest(API_EDITOR_RELATIONSHIPS_DELETE, variables);
		}
				
		/**
		 * Get profiles of the user.
		 */
		public function editorProfilesGet():void
		{
			sendRequest(API_EDITOR_PROFILES_GET);
		}	
			
		/**
		 * Update external profile of the user.
		 * 
		 * @param String sameas
		 * @param String seealso
		 * @param String label
		 */
		public function editorProfilesUpdate(sameas:String, seealso:String, label:String=null):void
		{
			var variables:URLVariables = new URLVariables();
			variables.sameas = sameas;
			variables.seealso = seealso;
			
			if(label != null && label != "")
				variables.label = label;
			
			sendRequest(API_EDITOR_PROFILES_UPDATE, variables);
		}
		
		/**
		 * Delete external profile of the user.
		 * 
		 * @param string sameas
		 */
		public function editorProfilesDelete(sameas:String):void
		{
			var variables:URLVariables = new URLVariables();
			variables.sameas = sameas;
			
			sendRequest(API_EDITOR_PROFILES_DELETE, variables);
		}
			
		/**
		 * Clean user profile.
		 */
		public function editorClean():void
		{
			sendRequest(API_EDITOR_CLEAN);
		}
		
		/**
		 * Get the profile of a user.
		 * 
		 * @param String uri The personal URI.
		 */
		public function browserProfile(uri:String):void
		{
			var variables:URLVariables = new URLVariables();
			variables.uri = uri;
			
			sendRequest(API_BROWSER_PROFILE, variables);
		}
		
		/**
		 * Get relationships of a user.
		 * 
		 * @param String uri The personal URI.
		 * @param Array types Provide an array with relationship types to filter
		 */
		public function browserRelationships(uri:String, types:Array=null):void
		{
			var variables:URLVariables = new URLVariables();
			variables.uri = uri;
			
			for each(var type:String in types)
				variables[type] = "";
			
			sendRequest(API_BROWSER_RELATIONSHIPS, variables);
		}
						
		/**
		 * Clean browser store.
		 */
		public function browserClean(uri:String=null):void
		{
			if(uri == null)
				sendRequest(API_BROWSER_CLEAN);
			else {
				var variables:URLVariables = new URLVariables();
				variables.uri = uri;
			
				sendRequest(API_BROWSER_CLEAN, variables);
			}
		}
		
		/**
		 * Close current request.
		 */
		public function close():void
		{
			urlLoader.close();
		}
		
		/**
		 * The result event listener for the authenticate method.
		 * Dispatches error or success event.
		 * 
		 * @param Event event
		 */
		private function resultHandler(event:Event):void
		{	
			var result:XML = new XML(URLLoader(event.target).data);

			if(isGood(result)) {
				_hasAuth = true;
				
				dispatchEvent(
					new ApiConnectorEvent(
						ApiConnectorEvent.SUCCESS_EVENT,
						result
					)
				);
			}
		}
		
		/**
		 * This method checks if an error occured as is returned with the xml result.
		 * If so the method dispatches an error event.
		 * 
		 * @param XML result
		 * @return Boolean True if everything is ok, false otherwise.
		 */
		private function isGood(result:XML):Boolean
		{
			if(result.error_code.length() > 0 && result.error_message.length() > 0) {
				
				trace(result);
				
				dispatchEvent(
					new ApiConnectorEvent(
						ApiConnectorEvent.ERROR_EVENT,
						null,
						result.error_code,
						result.error_message
					)
				);
				
				return false;
			}
			
			return true;
		}
		
		/**
		 * The error event handler.
		 *
		 * @param IOErrorEvent event
		 */
		private function errorHandler(event:IOErrorEvent):void
		{
			trace(event);

			dispatchEvent(
				new ApiConnectorEvent(
					ApiConnectorEvent.ERROR_EVENT,
					null,
					"HttpError",
					"Couldn't establish a connection to the server."
				)
			);
		}
		
		/**
		 * Send a request to Origo REST API.
		 */
		private function sendRequest(url:String, variables:URLVariables=null):void
		{			     				
			var urlRequest:URLRequest = new URLRequest(apiUrl + url);
			urlRequest.method = URLRequestMethod.POST;
			
			if(variables == null && authHash)
				variables = new URLVariables();
			
			if(authHash)
				variables.key = authHash;
			
			if(variables)
				urlRequest.data = variables;
            	
            urlLoader.load(urlRequest);
		}
	}
}