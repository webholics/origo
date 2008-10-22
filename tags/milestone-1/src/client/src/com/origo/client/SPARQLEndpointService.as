package com.origo.client
{
	import flash.events.EventDispatcher;
	
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.HTTPService;
	
	/**
	 * The SPARQLEndpointService class provides an easy way to 
	 * call a SPARQL endpoint over HTTP.
	 */
	public class SPARQLEndpointService extends EventDispatcher
	{
		/**
		 * Some SPARQL endpoints require an API key.
		 * If you need multiple keys for different SPARQL queries 
		 * @see #query()
		 */
		public var apiKey:String;
		
		/**
		 * The URL of the SPARQL endpoint.
		 */
		public var location:String;
		
		/**
		 * The HTTP method to use.
		 * Can be "GET" or "POST".
		 * 
		 * @default "GET"
		 */
		protected var httpMethod:String = "GET";

		/**
		 * The HTTPService object to call the SPARQL endpoint.
		 */
		protected var httpService:HTTPService;
		
		/**
		 * Constructor
		 * 
		 * @param loc The URL of the SPARQL endpoint.
		 */
		public function SPARQLEndpointService(loc:String):void
		{
			this.location = loc;
		}

		/**
		 * Use HTTP method POST to send query.
		 */
		public function setHttpMethodPost():void
		{
			this.httpMethod = "POST";
		}
		
		/**
		 * Use HTTP method GET to send query.
		 */
		public function setHttpMethodGet():void
		{
			this.httpMethod = "GET";
		}
		
		public function query(query:String):void
		{
			httpService = new HTTPService();
			httpService.destination = this.location;
			httpService.method = this.httpMethod;
			httpService.addEventListener("result", this.httpResult);
			httpService.addEventListener("fault", this.httpFault);
			
			var params:Object = {query:query, key:this.apiKey};
			
			httpService.send(params);
		}
		
		/**
		 * The ResultEvent listener for the HTTPService.
		 * Do not call this method directly.
		 */
		public function httpResult(event:ResultEvent):void
		{
			this.dispatchEvent(event);
		}
		
		/**
		 * The FaultEvent listener for the HTTPService.
		 * Do not call this method directly.
		 */
		public function httpFault(event:FaultEvent):void
		{
			this.dispatchEvent(event);
		}
	}
}