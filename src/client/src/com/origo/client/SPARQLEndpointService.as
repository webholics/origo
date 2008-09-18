package com.origo.client
{
	import mx.rpc.http.HTTPService;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.events.FaultEvent;
	
	/**
	 * The SPARQLEndpointService class provides an easy way to 
	 * call a SPARQL endpoint over HTTP.
	 */
	public final class SPARQLEndpointService
	{
		/**
		 * The HTTP method to use.
		 * Can be "GET" or "POST".
		 * 
		 * @default "GET"
		 */
		private var httpMethod:String = "GET";
		
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
		 * Constructor
		 * 
		 * @param loc The URL of the SPARQL endpoint.
		 */
		public function SPARQLEndpointService(loc:String)
		{
			location = loc;
		}

		/**
		 * Use HTTP method POST to send query.
		 */
		public function setHttpMethodPost()
		{
			httpMethod = "POST";
		}
		
		/**
		 * Use HTTP method GET to send query.
		 */
		public function setHttpMethodGet()
		{
			httpMethod = "GET";
		}
		
		public function query(query:String) 
		{
			
		}
	}
}