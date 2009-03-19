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
		 * constants used for loaders
		 */
        private static const AUTHENTICATE:String = "authenticate";
        
        /**
         * Api url constants
         */
        private static const API_TEST:String = "test";
		
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
		 * The prepared loaders for api calls.
		 */
		private var loaders:Array;

		public function ApiConnector()
		{
			if(instance) 
				throw new Error("ApiConnector and can only be accessed through ApiConnector.getInstance()");
				
			loaders = [];
			this.addLoader(AUTHENTICATE, authenticateHandler);
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
			sendRequest(AUTHENTICATE, API_TEST);
		}
		
		/**
		 * The result event listener for the authenticate method.
		 * Dispatches error or success event.
		 * 
		 * @param Event event
		 */
		private function authenticateHandler(event:Event):void
		{
			var result:XML = new XML(getLoader(AUTHENTICATE).data);
			trace(getLoader(AUTHENTICATE).data);
			if(result.code == 200) {
				_hasAuth = true;
				
				dispatchEvent(
					new ApiConnectorEvent(
						ApiConnectorEvent.SUCCESS_EVENT
					)
				);
			}
			else {
				dispatchEvent(
					new ApiConnectorEvent(
						ApiConnectorEvent.ERROR_EVENT,
						null,
						result.error_code,
						result.error_message
					)
				);
			}
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
		 * Private helper method to add a loader for later use.
		 */
		private function addLoader(name:String, completeHandler:Function):void
		{
			var loader:URLLoader = new URLLoader();
			loader.addEventListener(Event.COMPLETE, completeHandler);
			loader.addEventListener(IOErrorEvent.IO_ERROR, errorHandler);
			loader.addEventListener(SecurityErrorEvent.SECURITY_ERROR, errorHandler);
			loaders[name] = loader;
		}
		
		/**
		 * Private helper method to get a loader by name.
		 */
		private function getLoader(name:String):URLLoader
		{
			return loaders[name] as URLLoader;
		}
		
		/**
		 * Send a request to Origo REST API.
		 */
		private function sendRequest(loaderName:String, url:String, variables:URLVariables=null):void
		{			     				
			var urlRequest:URLRequest = new URLRequest(apiUrl + url);
			urlRequest.method = URLRequestMethod.POST;
			
			if(variables == null && authHash)
				variables = new URLVariables();
			
			if(authHash)
				variables.key = authHash;
			
			if(variables)
				urlRequest.data = variables;
            	
            var urlLoader:URLLoader = getLoader(loaderName);
            urlLoader.load(urlRequest);
		}
	}
}