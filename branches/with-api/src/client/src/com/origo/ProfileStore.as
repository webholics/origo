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
		private var properties:Array;
		
		/**
		 * Relationships
		 */
		[Bindable]
		private var relationships:Array;
		
		/**
		 * External profiles
		 */
		[Bindable]
		private var profiles:Array;
		
		public function ProfileStore()
		{
			if(instance) 
				throw new Error("ProfileStore can only be accessed through ProfileStore.getInstance()");
		}
		
		public static function getInstance():ApiConnector 
		{
			return instance;
		}
	}
}