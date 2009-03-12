package com.origo.client
{
	/**
	 * A helper class to format inputs.
	 */
	public class InputHelper
	{
		public function InputHelper()
		{}
		
		/**
		 * Remove leading and trailing spaces.
		 * @param str A string to trim.
		 * @return The trimmed string.
		 */
		 public static function trim(str:String):String
		 {
		 	var pattern:RegExp = new RegExp("^[ \t]+|[ \t]+$");
		 	return str.replace(pattern, "");
		 }

		/**
		 * Remove "email:" from uri to get the email address.
		 * @param uri The uri to remove "email:" from.
		 * @return The email address.
		 */
		public static function UriToEmail(uri:String):String
		{
			var pattern:RegExp = new RegExp("^[ \t]*email:");
			return uri.replace(pattern, "");
		}
		
		/**
		 * Add "email:" to the email address.
		 * @param email The email address
		 * @return A correct email uri.
		 */
		public static function EmailToUri(email:String):String
		{
			return "email:" + email;
		}
	}
}