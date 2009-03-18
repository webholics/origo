package com.origo
{
	import flash.events.Event;
	
	/**
	 * ApiConnectorErrorEvent
	 * 
	 * This is event is dispatched when an error occured.
	 */
	public class ApiConnectorErrorEvent extends Event
    {
		public function ApiConnectorErrorEvent(type:String, code:String, message:String) 
		{
			super(type);
    
			this.code = code;
			this.message = message;
        }

		public static const ERROR_EVENT:String = "errorEvent";
		
		/**
		 * A machine understandable error code.
		 */
		public var code:String;
		
		/**
		 * A human readable error message.
		 */
		public var message:String;

		override public function clone():Event 
		{
			return new ApiConnectorErrorEvent(type, code, message);
		}
	}
}