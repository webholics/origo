package com.origo.browser
{
	import com.adobe.flex.extras.controls.springgraph.Item;

	public class BrowserItem extends Item
	{
		/**
		 * True if data was fully loaded via browser/profile api method.
		 */
		public var loaded:Boolean;
		 
		public function BrowserItem(id:String=null)
		{
			super(id);
			
			loaded = false;
		}
		
	}
}