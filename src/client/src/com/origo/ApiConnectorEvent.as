package com.origo
{
	import flash.events.Event;
	
	/**
	 * ApiConnectorEvent
	 */
	public class ApiConnectorEvent extends Event
    {
		public function ApiConnectorEvent(type:String, data:Object=null, code:String="Success", message:String="") 
		{
			super(type);
    
    		this.data = data;
			this.code = code;
			this.message = message;
        }

		public static const ERROR_EVENT:String = "errorEvent";
		public static const SUCCESS_EVENT:String = "successEvent";
		
		/**
		 * The data received by the api call.
		 */
		public var data:Object;
		
		/**
		 * A machine understandable error code or "Success".
		 */
		public var code:String;
		
		/**
		 * A human readable error message.
		 */
		public var message:String;

		override public function clone():Event 
		{
			return new ApiConnectorEvent(type, data, code, message);
		}
	}
}