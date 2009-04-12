/**
 * Origo - social client
 *
 * @copyright Copyright (c) 2008-2009 Mario Volke
 * @author    Mario Volke <mario.volke@webholics.de>
 */

package com.origo
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
	}
}