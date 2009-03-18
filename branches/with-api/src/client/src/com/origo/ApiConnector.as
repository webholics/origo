package com.origo
{
	import com.adobe.net.URI;
	
	import flash.events.ErrorEvent;
	import flash.events.EventDispatcher;
	
	import mx.utils.Base64Encoder;
	
	import org.httpclient.HttpClient;
	import org.httpclient.HttpRequest;
	import org.httpclient.events.*;
	import org.httpclient.http.Get;

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
		 * Authorization header
		 */
		private var authorizationHeader:String = null;

		public function ApiConnector()
		{
			if(instance) 
				throw new Error("ApiConnector and can only be accessed through ApiConnector.getInstance()");
				
			/*service = new HTTPService();
			service.method = "get";
			service.useProxy = false;
			service.resultFormat = "e4x";
			service.showBusyCursor = false;
			service.requestTimeout = 30;
			service.addEventListener(FaultEvent.FAULT, serviceFault);
			*/
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
			authorizationHeader = "Basic " + encoder.toString();
			
			// check credentials via test method provided by the api
			/*service.url = apiUrl + "test";
			service.addEventListener(ResultEvent.RESULT, authenticateResult);
			resultEventListener = authenticateResult;
			service.send();*/
			
			sendRequest(apiUrl + "test", authenticationResult);
		}
		
		/**
		 * The result event listener for the authenticate method.
		 * Dispatches error or success event.
		 * 
		 * @param HttpResponseEvent event
		 */
		/*private function authenticateResult(event:HttpResponseEvent):void
		{		
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
		}*/
		private function authenticationResult(event:HttpDataEvent):void
		{
			var result:XML = new XML(event.readUTFBytes());
			trace(event.readUTFBytes());
			if(result.code == 200) {
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
						result.error_code,
						result.error_message
					)
				);
			}
		}
		
		/**
		 * The event handler for HttpErrorEvent from HTTPClient.
		 *
		 * @param ErrorEvent event
		 */
		private function serviceFault(event:ErrorEvent):void
		{		
			trace(event);
				
			dispatchEvent(
				new ApiConnectorErrorEvent(
					ApiConnectorErrorEvent.ERROR_EVENT,
					"HttpError",
					"Couldn't establish a connection to the server."
				)
			);
		}
		
		/**
		 * Send a request to Origo REST API.
		 */
		private function sendRequest(url:String, result:Function, params:Array=null):void
		{			     
			var request:HttpRequest = new Get();
			if(authorizationHeader != null)
				request.addHeader("Authorization", authorizationHeader);
			if(params != null)
				request.setFormData(params);
			
			var listener:HttpListener = new HttpListener({
				onData:		result,
				onError: 	serviceFault
			});
      
			var client:HttpClient = new HttpClient();
			client.timeout = 5000;
			client.listener = listener;
			client.request(new URI(url), request);
		}
	}
}