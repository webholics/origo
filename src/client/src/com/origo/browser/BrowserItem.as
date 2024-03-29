/**
 * Origo - social client
 *
 * @copyright Copyright (c) 2008-2009 Mario Volke
 * @author    Mario Volke <mario.volke@webholics.de>
 */

package com.origo.browser
{
	import com.adobe.flex.extras.controls.springgraph.Item;

	public class BrowserItem extends Item
	{
		/**
		 * True if properties were fully loaded via browser/profile api method.
		 */
		public var loaded:Boolean;
		
		[Bindable] public var properties:XML;
		[Bindable] public var relationships:XML;
		 
		public function BrowserItem(id:String=null)
		{
			super(id);
			
			loaded = false;
		}
		
	}
}