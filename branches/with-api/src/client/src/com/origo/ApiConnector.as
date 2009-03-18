package com.origo
{
	import flash.events.EventDispatcher;
	
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.mxml.HTTPService;
	import mx.utils.Base64Encoder; 

	/**
	 * ApiConnector
	 * 
	 * Use this class to communicate with the Origo REST API.
	 * The class is also used as data model for already received data.
	 */
	[Event(name="successEvent", type="ApiConnectorSuccessEvent")]
	[Event(name="errorEvent", type="ApiConnectorErrorEvent")]
	public class ApiConnector extends EventDispatcher
	{
		/**
		 * Singleton instance
		 */
		private static var instance:ApiConnector = new ApiConnector();
		
		/**
		 * The base url of the REST-API.
		 */
		private var apiUrl:String = "";
		
		/**
		 * Has user authentication?
		 */
		private var _hasAuth:Boolean = false;
		
		/**
		 * The HTTPService to send api requests.
		 */
		private var service:HTTPService; 
				
		/**
		 * Current result event listener for service.
		 */
		private var resultEventListener:Function = null;

		public function ApiConnector()
		{
			if(instance) 
				throw new Error("ApiConnector and can only be accessed through ApiConnector.getInstance()");
				
			service = new HTTPService();
			service.method = "get";
			service.useProxy = false;
			service.resultFormat = "e4x";
			service.showBusyCursor = false;
			service.requestTimeout = 30;
			service.addEventListener(FaultEvent.FAULT, serviceFault);
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
		 * Listen to success and error events!
		 * 
		 * @param String username
		 * @param String password
		 */
		public function authenticate(username:String, password:String):void
		{
			// construct basic auth header
			var encoder:Base64Encoder = new Base64Encoder();
			encoder.insertNewLines = false;
			encoder.encode(username + ":" + password);
			service.headers["Authorization"] = "Basic " + encoder.toString();
			
			// check credentials via test method provided by the api
			service.url = apiUrl + "test";
			service.addEventListener(ResultEvent.RESULT, authenticateResult);
			resultEventListener = authenticateResult;
			trace(service.url);
			service.send();
		}
		
		/**
		 * The result event listener for the authenticate method.
		 * Dispatches error or success event.
		 * 
		 * @param ResultEvent event
		 */
		private function authenticateResult(event:ResultEvent):void
		{
			removeResultEventListener();
			
			if(event.result.code == 200) {
				_hasAuth = true;
				
				dispatchEvent(
					new ApiConnectorSuccessEvent(
						ApiConnectorSuccessEvent.SUCCESS_EVENT
					)
				);
			}
			else {
				_hasAuth = false;
				
				dispatchEvent(
					new ApiConnectorErrorEvent(
						ApiConnectorErrorEvent.ERROR_EVENT,
						event.result.error_code,
						event.result.error_message
					)
				);
			}
		}
		
		/**
		 * The event handler for FaultEvent from HTTPService.
		 *
		 * @param event The FaultEvent result.
		 */
		private function serviceFault(event:FaultEvent):void
		{
			removeResultEventListener();
			
			trace(event.fault.message);
			
			dispatchEvent(
				new ApiConnectorErrorEvent(
					ApiConnectorErrorEvent.ERROR_EVENT,
					"HttpError",
					"Couldn't establish a connection to the server."
				)
			);
		}
		
		/**
		 * Remove old result event listener
		 */
		private function removeResultEventListener():void
		{
			if(resultEventListener != null) {
				service.removeEventListener(ResultEvent.RESULT, resultEventListener);
				resultEventListener = null;
			}
		}
	}
}