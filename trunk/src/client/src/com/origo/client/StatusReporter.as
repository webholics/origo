package com.origo.client
{
	[Bindable]
	public class StatusReporter
	{
		private var _status:String;
		
		public function StatusReporter()
		{
			_status = "";
		}
		
		public function get status():String
		{
			return _status;
		}
		
		public function set status(value:String):void
		{
			_status = value;
		}
		
		public function clearStatus():void
		{
			_status = "";
		}
	}
}