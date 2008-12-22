package com.origo.client
{
	import flash.events.Event;
	import flash.events.EventDispatcher;
	
	import mx.collections.ArrayCollection;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.mxml.HTTPService;
	
	public class TripleStore extends EventDispatcher 
	{
		private var url:String;
		private var key:String;
		private var api:HTTPService;
		
		private var queries:ArrayCollection;
		private var _results:ArrayCollection;
		private var currentQuery:Number;
		
		/**
		 * 2 = delete old backup graph (maybe there is a backup graph from the last transaction)
		 * 1 = copy data to backup graph
		 * 0 = delete backup graph
		 * -1 = nothing to do
		 */
		private var backupPhase:Number;
		/**
		 * 2 = check if original graph is empty
		 * 1 = copy from backup graph to original graph
		 * 0 = delete backup graph
		 * -1 = not a restore backup phase
		 */
		private var restoreBackupPhase:Number;
		private var currentGraph:String;
		
		private var reporter:StatusReporter;
		
		private var queryExecuting:Boolean;
		
		private var sparql:Namespace = new Namespace("http://www.w3.org/2005/sparql-results#");
		
		public function TripleStore(url:String, key:String="", reporter:StatusReporter=null)
		{
			this.url = url;
			this.key = key;
			
			this.api = new HTTPService;
			this.api.url = this.url;
			this.api.method = "post";
			this.api.useProxy = false;
			this.api.resultFormat = "e4x";
			this.api.showBusyCursor = true;
			this.api.addEventListener(FaultEvent.FAULT, this.apiFault);
			this.api.addEventListener(ResultEvent.RESULT, this.apiResult);
			
			this.queryExecuting = false;
			
			this.reporter = reporter;
			
			this.queries = new ArrayCollection;
			this._results = new ArrayCollection;
			this.currentQuery = 0;
			
			this.currentGraph = "";
			this.backupPhase = -1;
			this.restoreBackupPhase = -1;
		}
		
		/**
		 * The event handler for FaultEvent from HTTPService.
		 * Don't call this method by hand.
		 * @param event The FaultEvent result.
		 */
		private function apiFault(event:FaultEvent):void
		{
			if(restoreBackupPhase >= 0) {
				restoreBackupPhase = -1;
			}
			else {
				// clear query queue
				this.queries.removeAll();
				this._results.removeAll();
			}
			
			queryExecuting = false;

			this.reporter.status = "An error occured while communicating with the sparql endpoint.";
		}
		
		/**
		 * The event handler for ResultEvent from HTTPService.
		 * Don't call this method by hand.
		 * @param event The ResultEvent result.
		 */
		private function apiResult(event:ResultEvent):void
		{
			// check if this is a restore backup
			if(this.restoreBackupPhase >= 0) {
				if(this.restoreBackupPhase == 0) {
					this.queryExecuting = false;
					
					// dispatch result event
					this.dispatchEvent(new Event("result"));
				}
				else {
					--this.restoreBackupPhase;
					runRestoreBackupPhase();
				}
				
				return;
			}
			
			// otherwise it is a transaction
			if(this.backupPhase >= 2) {	
				--this.backupPhase;
				runBackupPhase();
				
				return;
			}
			
			// check if all queries are executed
			if(this.currentQuery >= this.queries.length-1) {
				if(this.currentQuery == this.queries.length-1) {
					// save last result
					this._results.addItem(event.result);
				}
				
				// check if we need to clean the backup graph
				if(this.backupPhase == 0) {
					runBackupPhase();
					--this.backupPhase;
					++this.currentQuery;
				}
				else {
					this.queryExecuting = false;
					this.queries.removeAll();
					
					// dispatch result event
					this.dispatchEvent(new Event("result"));
				}
			}
			else {
				if(this.backupPhase >= 1) {
					--this.backupPhase;
				}
				else {
					// save last result
					this._results.addItem(event.result);
				}
				
				// execute next query
				this.currentQuery++;
				
				this.api.send({
					query: this.queries[this.currentQuery],
					key: this.key
				});
			}
		}

		/**
		 * Directly execute a query without backing up any data in the triple store.
		 * Don't forget to add an event listener for "result".
		 * @param query The SPARQL query to execute.
		 * @return Returns true if it was possible to execute the query.
		 */
		public function executeQuery(query:String):Boolean
		{			
			if(enqueueQuery(query) && executeQueries()) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Push a query into the transaction queue to be executed with the next executeQueries() call.
		 * @param query The SPARQL query to be enqueued.
		 * @return Returns true if it was possible to enqueue the query.
		 */
		public function enqueueQuery(query:String):Boolean
		{
			if(this.queryExecuting) {
				return false;
			}
			
			this.queries.addItem(query);
			
			return true;
		}
		
		/**
		 * Execute the all enqueued queries.
		 * @param graph If graph is not empty then the graph will be backuped before the transaction begins.
		 * @return Returns true if it was possible to execute the transaction.
		 */
		public function executeQueries(graph:String=""):Boolean
		{
			if(this.queryExecuting || this.queries.length == 0) {
				return false;
			}
			
			this.queryExecuting = true;
			this._results.removeAll();
			
			this.currentGraph = graph;
			this.restoreBackupPhase = -1;
			
			if(graph == "") {
				this.backupPhase = -1;
				this.currentQuery = 0;
				
				this.api.send({
					query: this.queries[0],
					key: this.key
				});
			}
			else {
				this.backupPhase = 2;
				this.currentQuery = 0;
				
				runBackupPhase();
			}
			
			return true;
		}
		
		/**
		 * Get the results from the last transaction in the order of execution.
		 * @return The results as e4x objects.
		 */
		public function get results():Array
		{
			return this._results.source;
		}
		
		/**
		 * From time to time the consistency of the triple store should be checked.
		 * If something went wrong within a transaction the graph can be restored.
		 * @param graph The URI of the graph to be checked.
		 * @return Returns true if it was possible to execute the queries.
		 */
		public function tryToRestoreIfEmpty(graph:String):Boolean
		{
			if(this.queryExecuting || graph == "") {
				return false;
			}
			
			this.queryExecuting = true;
			this._results.removeAll();
			
			this.currentGraph = graph;
			this.restoreBackupPhase = 2;
				
			runRestoreBackupPhase();

			return true;
		}
		
		/**
		 * Runs the current backup phase.
		 */
		private function runBackupPhase():void
		{
			switch(this.backupPhase) {
				case 2:
				case 0:				
					this.api.send({
						query: "DELETE FROM <" + getBackupGraph(this.currentGraph) + ">",
						key: this.key
					});
					break;
				case 1:
					this.api.send({
						query: "INSERT INTO <" + getBackupGraph(this.currentGraph) + "> CONSTRUCT { ?s ?p ?o . } WHERE { GRAPH <" + this.currentGraph + "> { ?s ?p ?o . } }",
						key: this.key
					});
					break;
				default:
					break;
			}
		}
		
		/**
		 * Runs the current restore backup phase.
		 */
		private function runRestoreBackupPhase():void
		{
			switch(this.restoreBackupPhase) {
				case 2:
					this.api.send({
						query: "SELECT ?s ?p ?o WHERE { GRAPH <" + this.currentGraph + "> { ?s ?p ?o . } }",
						key: this.key
					});
					break;
				case 1:
					if(this.api.lastResult.sparql::results.sparql::result.length() == 0) {
						this.api.send({
							query: "INSERT INTO <" + this.currentGraph + "> CONSTRUCT { ?s ?p ?o . } WHERE { GRAPH <" + getBackupGraph(this.currentGraph) + "> { ?s ?p ?o . } }",
							key: this.key
						});
					}
					else {
						this.restoreBackupPhase = -1;
						this.queryExecuting = false;
					
						// dispatch result event
						this.dispatchEvent(new Event("result"));
					}
					break;
				case 0:				
					this.api.send({
						query: "DELETE FROM <" + getBackupGraph(this.currentGraph) + ">",
						key: this.key
					});
					break;
				default:
					break;
			}
		}
		
		/**
		 * This method constructs an URI for the backup graph.
		 * @param graph The original graph URI.
		 * @return String The URI of the backup graph.
		 */
		private function getBackupGraph(graph:String):String
		{
			return graph + ":backup";
		}
	}
}