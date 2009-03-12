package com.origo.client
{
	import flash.utils.Timer;
	import flash.events.TimerEvent;
	
	[Bindable]
	public class StatusReporter
	{
		private var _status:String;
		private var timer:Timer;
		
		public function StatusReporter()
		{
			_status = "";
			timer = null;
		}
		
		public function get status():String
		{
			return _status;
		}
		
		public function set status(value:String):void
		{
			if(timer) {
				timer.stop();
				timer = null;
			}
			
			_status = value;
		}
		
		/**
		 * Clear the status but wait a few seconds.
		 * @param sec Status will be cleared in sec seconds.
		 */
		public function clearStatusInSeconds(sec:Number):void
		{
			timer = new Timer(sec * 1000, 1);
			timer.addEventListener(TimerEvent.TIMER, onTimer);
			timer.start();
		}
		
		private function onTimer(event:TimerEvent):void
        {
            clearStatus();
            timer = null;
        }
		
		/**
		 * Clear the status. Status text will be set to "".
		 */
		public function clearStatus():void
		{
			status = "";
		}
	}
}