<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

abstract class VikError {

	/**
	 * Wrapper error method for the handleError() method.
	 *
	 * @throws  Exception 	Throws an exception only when the code is not null and the Joomla version is equals or higher than 3.5.0
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 *
	 * @return  JException|string  $error  The thrown JException object
	 *
	 * @see     JError::handleError()
	 */
	public static function raiseError($code, $message) {
		return self::handleError(E_ERROR, $code, $message);
	}

	/**
	 * Wrapper warning method for the handleError() method.
	 *
	 * @throws  Exception 	Throws an exception only when the code is not null and the Joomla version is equals or higher than 3.5.0
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 *
	 * @return  JException|string  $error  The thrown JException object
	 *
	 * @see     JError::handleError()
	 */
	public static function raiseWarning($code, $message) {
		return self::handleError(E_WARNING, $code, $message);
	}

	/**
	 * Wrapper notice method for the handleError() method.
	 *
	 * @throws  Exception 	Throws an exception only when the code is not null and the Joomla version is equals or higher than 3.5.0
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 *
	 * @return  JException|string  $error  The thrown JException object
	 *
	 * @see     JError::handleError()
	 */
	public static function raiseNotice($code, $message) {
		return self::handleError(E_NOTICE, $code, $message);
	}

	/**
	 * Handle the error in the proper way.
	 *
	 * @throws  Exception 	Throws an exception only when the code is not null and the Joomla version is equals or higher than 3.5.0
	 *
	 * @param   string  $level 	The error level - use any of PHP's own error levels for
	 *                          this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR,
	 *                          E_USER_WARNING, E_USER_NOTICE.
	 * @param   string  $code  	The application-internal error code for this error
	 * @param   string  $msg   	The error message, which may also be shown the user if need be.
	 *
	 * @return  JException|string  $error  The thrown JException object
	 *
	 */
	protected static function handleError($level, $code, $message) {
		if( !empty($code) ) {

			static $throwable = null;

			// identify Joomla version
			if( $throwable === null ) {

				$jv = new JVersion();
				$v = $jv->getShortVersion();

				$throwable = ( version_compare($v, '3.5') >= 0 );

			}

			if( $throwable ) {
				// throw exception whether Joomla is equals or greater than 3.5.0
				throw new Exception($message, $code);
			} else {
				// launch default JError handler
				switch($level) {
					case E_ERROR:
						return JError::raiseError($code, $message);
						break;
					case E_WARNING:
						return JError::raiseWarning($code, $message);
						break;
					case E_NOTICE:
						return JError::raiseNotice($code, $message);
						break;
				}
				return null;
			}

		}

		JFactory::getApplication()->enqueueMessage($message, ($level == E_NOTICE ? 'notice' : 'error'));

		return '';
	}

}

?>