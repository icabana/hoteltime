<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

defined('VIKREQUEST_ALLOWRAW') or define('VIKREQUEST_ALLOWRAW', 2);
defined('VIKREQUEST_ALLOWHTML') or define('VIKREQUEST_ALLOWHTML', 4);

abstract class VikRequest {

	/**
	 * Fetches and returns a given variable.
	 *
	 * The default behaviour is fetching variables depending on the
	 * current request method: GET and HEAD will result in returning
	 * an entry from $_GET, POST and PUT will result in returning an
	 * entry from $_POST.
	 *
	 * You can force the source by setting the $hash parameter:
	 *
	 * post    $_POST
	 * get     $_GET
	 * files   $_FILES
	 * cookie  $_COOKIE
	 * env     $_ENV
	 * server  $_SERVER
	 * method  via current $_SERVER['REQUEST_METHOD']
	 * default $_REQUEST
	 *
	 * @param   string   $name     	Variable name.
	 * @param   string   $default  	Default value if the variable does not exist.
	 * @param   string   $hash     	Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 * @param   string   $type     	The return type for the variable:
	 * 								INT:		An integer, or an array of integers,
	 *                           	UINT:		An unsigned integer, or an array of unsigned integers,
	 *                           	FLOAT:		A floating point number, or an array of floating point numbers,
	 *                           	BOOLEAN:	A boolean value,
	 *                           	WORD:		A string containing A-Z or underscores only (not case sensitive),
	 *                           	ALNUM:		A string containing A-Z or 0-9 only (not case sensitive),
	 *                           	CMD:		A string containing A-Z, 0-9, underscores, periods or hyphens (not case sensitive),
	 *                           	BASE64:		A string containing A-Z, 0-9, forward slashes, plus or equals (not case sensitive),
	 *                           	STRING:		A fully decoded and sanitised string (default),
	 *                           	HTML:		A sanitised string,
	 *                           	ARRAY:		An array,
	 *                           	PATH:		A sanitised file path, or an array of sanitised file paths,
	 *                           	TRIM:		A string trimmed from normal, non-breaking and multibyte spaces
	 *                           	USERNAME:	Do not use (use an application specific filter),
	 *                           	RAW:		The raw string is returned with no filtering,
	 *                           	unknown:	An unknown filter will act like STRING. If the input is an array it will return an
	 *                              			array of fully decoded and sanitised strings.
	 * @param   integer  $mask     Filter mask for the variable.
	 *
	 * @return  mixed  Requested variable.
	 */

	public static function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0) {

		$input = JFactory::getApplication()->input;

		// Ensure hash is uppercase
		$hash = strtoupper($hash);

		if( $hash === 'METHOD' ) {
			$hash = strtoupper($input->server->get('REQUEST_METHOD'));
		}

		// Get the input hash
		switch( $hash ) {
			case 'GET':
				$input = &$input->get;
				break;
			case 'POST':
				$input = &$input->post;
				break;
			case 'REQUEST':
				$input = &$input->request;
				break;
			case 'FILES':
				$input = &$input->files;
				break;
			case 'COOKIE':
				$input = &$input->cookie;
				break;
			case 'SERVER':
				$input = &$input->server;
				break;
			default:
				// leave default input
				break;
		}

		if( $mask == VIKREQUEST_ALLOWRAW || $mask == VIKREQUEST_ALLOWHTML ) {
			// If the allow html/raw flag is set, do not modify the variable
			// safe html filter may not be supported by JFilterInput
			$type = 'raw';
		}

		if( $hash === 'FILES' ) {
			// adapter for JRequest::getVar in case of multi-dim file upload.
			// $input returns the array with the keys reset while getVar used to return an associative array like the superglobal FILES
			$arr = $input->get($name, $default, $type);
			if(count($arr) > 0 && array_key_exists(0, $arr)) {
				// re-arrange the array like before for code compatibility
				/*
				Array
				(
				    [name] => Array
				        (
				            [0] => x.png
				            [1] => y.jpg
				        )
				    [type] => Array
				        (
				            [0] => image/png
				            [1] => image/jpeg
				        )
				)
				*/
				$legacy_map = array();
				foreach($arr as $ak => $av) {
					foreach($av as $updk => $updv) {
						if(!array_key_exists($updk, $legacy_map)) {
							$legacy_map[$updk] = array();
						}
						$legacy_map[$updk][] = $updv;
					}
				}
				return $legacy_map;
			}
		}

		return $input->get($name, $default, $type);

	}

	/**
	 * Fetches and returns a given filtered variable. The integer
	 * filter will allow only digits and the - sign to be returned. This is currently
	 * only a proxy function for getVar().
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  integer  Requested variable.
	 */

	public static function getInt($name, $default = 0, $hash = 'default') {
		return self::getVar($name, (int)$default, $hash, 'int');
	}

	/**
	 * Fetches and returns a given filtered variable. The unsigned integer
	 * filter will allow only digits to be returned. This is currently
	 * only a proxy function for getVar().
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  integer  Requested variable.
	 */

	public static function getUInt($name, $default = 0, $hash = 'default') {
		return self::getVar($name, $default, $hash, 'uint');
	}

	/**
	 * Fetches and returns a given filtered variable.  The float
	 * filter only allows digits and periods.  This is currently
	 * only a proxy function for getVar().
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  float  Requested variable.
	 */

	public static function getFloat($name, $default = 0.0, $hash = 'default') {
		return self::getVar($name, (float)$default, $hash, 'float');
	}

	/**
	 * Fetches and returns a given filtered variable. The bool
	 * filter will only return true/false bool values. This is
	 * currently only a proxy function for getVar().
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  boolean  Requested variable.
	 */

	public static function getBool($name, $default = false, $hash = 'default') {
		return self::getVar($name, $default, $hash, 'bool');
	}

	/**
	 * Fetches and returns a given filtered variable. The word
	 * filter only allows the characters [A-Za-z_]. This is currently
	 * only a proxy function for getVar().
	 *
	 * @param   string  $name     Variable name.
	 * @param   string  $default  Default value if the variable does not exist.
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 *
	 * @return  string  Requested variable.
	 */

	public static function getWord($name, $default = '', $hash = 'default') {
		return self::getVar($name, $default, $hash, 'word');
	}

	/**
	 * Cmd (Word and Integer) filter
	 *
	 * Fetches and returns a given filtered variable. The cmd
	 * filter only allows the characters [A-Za-z0-9.-_]. This is
	 * currently only a proxy function for getVar().
	 *
	 * @param   string  $name     Variable name
	 * @param   string  $default  Default value if the variable does not exist
	 * @param   string  $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
	 *
	 * @return  string  Requested variable
	 */

	public static function getCmd($name, $default = '', $hash = 'default') {
		return self::getVar($name, $default, $hash, 'cmd');
	}

	/**
	 * Fetches and returns a given filtered variable. The string
	 * filter deletes 'bad' HTML code, if not overridden by the mask.
	 * This is currently only a proxy function for getVar().
	 *
	 * @param   string   $name     Variable name
	 * @param   string   $default  Default value if the variable does not exist
	 * @param   string   $hash     Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
	 * @param   integer  $mask     Filter mask for the variable
	 *
	 * @return  string   Requested variable
	 */

	public static function getString($name, $default = '', $hash = 'default', $mask = 0) {
		return self::getVar($name, $default, $hash, 'string', $mask);
	}

	/**
	 * Set a variable in one of the request variables.
	 *
	 * @param   string   $name       Name
	 * @param   string   $value      Value
	 * @param   string   $hash       Hash
	 * @param   boolean  $overwrite  Boolean
	 *
	 * @return  string   Previous value
	 */

	public static function setVar($name, $value = null, $hash = 'method', $overwrite = true) {

		$input = &JFactory::getApplication()->input;

		// Ensure hash is uppercase
		$hash = strtoupper($hash);

		/*
		//Do not get the request method automatically from $_SERVER or it could result into GET and not set the 'view' in the REQUEST
		if( $hash === 'METHOD' ) {
			$hash = strtoupper($input->server->get('REQUEST_METHOD'));
		}
		*/

		// Get the input hash
		switch ($hash) {
			case 'GET':
				$input = &$input->get;
				break;
			case 'POST':
				$input = &$input->post;
				break;
			case 'REQUEST':
				$input = &$input->request;
				break;
			case 'FILES':
				$input = &$input->files;
				break;
			case 'COOKIE':
				$input = &$input->cookie;
				break;
			case 'SERVER':
				$input = &$input->server;
				break;
			default:
				// leave default input
				break;
		}

		$previous = $input->get($name, null, 'raw');

		// If overwrite is true, makes sure the variable hasn't been set yet
		if (!$overwrite && $previous !== null) {
			return $previous;
		}

		$input->set($name, $value);

		return $value;
	}

}

?>