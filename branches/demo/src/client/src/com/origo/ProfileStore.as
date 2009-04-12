/**
 * Origo - social client
 *
 * @copyright Copyright (c) 2008-2009 Mario Volke
 * @author    Mario Volke <mario.volke@webholics.de>
 */

package com.origo
{
	public class ProfileStore
	{
		/**
		 * Singleton instance
		 */
		private static var instance:ProfileStore = new ProfileStore();
		
		/**
		 * Person URI
		 */
		[Bindable]
		public var id:String;
		
		/**
		 * Unique properties
		 */
		[Bindable]
		public var properties:XML;
		
		/**
		 * Relationships
		 */
		[Bindable]
		public var relationships:XML;
		
		/**
		 * External profiles
		 */
		[Bindable]
		public var profiles:XML;
		
		public function ProfileStore()
		{
			if(instance) 
				throw new Error("ProfileStore can only be accessed through ProfileStore.getInstance()");
		}
		
		public static function getInstance():ProfileStore 
		{
			return instance;
		}
	}
}