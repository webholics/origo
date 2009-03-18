package com.origo
{
	import flash.events.Event;
	
	/**
	 * ApiConnectorSuccessEvent
	 * 
	 * This is event is dispatched when everything worked just fine.
	 * The event itself doesn't contain any information.
	 */
	public class ApiConnectorSuccessEvent extends Event
    {
		public function ApiConnectorSuccessEvent(type:String) 
		{
			super(type);
        }

		public static const SUCCESS_EVENT:String = "successEvent";
		
		override public function clone():Event 
		{
			return new ApiConnectorSuccessEvent(type);
		}
	}
}