package com.origo.client
{
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.mxml.HTTPService;
	
	/**
	 * The SPARQLEndpointService class provides an easy way to 
	 * call a SPARQL endpoint over HTTP.
	 */
	public class SPARQLEndpointService extends HTTPService
	{
		/**
		 * Some SPARQL endpoints require an API key.
		 */
		public var key:String;
		
		/**
		 * Constructor
		 */
		public function SPARQLEndpointService(rootURL:String = null, destination:String = null, key:String = null):void
		{
			super(rootURL, destination);
			this.key = key;
		}
		
		public function query(query:String):void
		{	
			if(key != null) {
				trace("asdf");
				this.send({query:query, key:this.key});
			}
			else
				this.send({query:query});
		}
	}
}