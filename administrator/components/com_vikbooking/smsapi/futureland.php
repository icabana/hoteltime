<?php
/**
 * @package     VikBooking
 * @subpackage  com_vikbooking
 * @author      e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

class VikSmsApi {
	
	private $order_info;
	private $params;
	private $log = '';
	
	private $BASE_URI = 'https://www.futureland.it/gateway/futuresend.asp';
	
	public static function getAdminParameters() {
		return array(
			'username' => array(
				'label' => 'Username',
				'type' => 'text'
			),
			
			'password' => array(
				'label' => 'Password',
				'type' => 'text'
			),
			
			'mittente' => array(
				'label' => 'Sender Name//Max 11 characters',
				'type' => 'text'
			),
			
			'prefix' => array(
				'label' => 'Default Phone Prefix//This will be used only in case the prefix will be missing. It should be the prefix of your country of residence.',
				'type' => 'text'
			)
		);
	}
	
	public function __construct ($order, $params=array()) {
		$this->order_info=$order;
		
		$this->params = !empty($params) ? $params : $this->params;
	}
	
	///// SEND MESSAGE /////
	
	//$date = new DateTime ( "2014-02-08 11:14:15" );
	//$when = $date->format ( DateTime::ISO8601 );

	public function sendMessage($phone_number, $msg_text, $when=NULL) {
		if (empty($phone_number) || empty($msg_text)) {
			$this->log = 'missing phone number or text to send';
			return false;
		}
		
		return $this->_send('SMS_ALT', $phone_number, $msg_text, $when);
	}

	///// ESTIMATE CREDIT /////
	
	public function estimate($phone_number, $msg_text) {
		$phone_number = '1234567890';
		$msg_text = 'TEST';
		return $this->_send('GET_MONEY', $phone_number, $msg_text, NULL);
	}

	private function _send($type, $phone_number, $msg_text, $when=NULL) {
		$this->log = '';
		
		$phone_number = trim(str_replace(" ", "", $phone_number));
		
		if (substr($phone_number, 0, 1) != '+') {
			if (substr($phone_number, 0, 2) == '00') {
				$phone_number = '+'.substr($phone_number, 2);
			} else {
				$phone_number = $this->params['prefix'].$phone_number;
			}
		}
		
		$post = array (
			'username' => urlencode($this->params['username']),
			'password' => urlencode($this->params['password']),
			'destinatari' => urlencode($phone_number),
			'tipo' => urlencode($type),
			'dati' => urlencode($msg_text),
			'mittente' => urlencode($this->params['mittente']),
		);
		
		$array_result = $this->sendPost($this->BASE_URI, $post);
		
		if ($array_result['from_smsh']) {
			return $this->parseResponse($array_result, $type);
		} else {
			return false;
		}
	} 
	
	private function sendPost($complete_uri, $data) {
		$post = '';
		foreach ($data as $k => $v) {
			$post .= "$k=$v".'&';
		}
		$post = rtrim($post, '&');
		
		$array_result = array(
			'from_smsh' => false,
			'smsh_response_status' => 500,
			'smsh_response' => null
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $complete_uri);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, count($data));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		if ($curl_errno = curl_errno($ch)) {
			$err = curl_error($ch);
			$this->log .= 'CURL Error ('.$curl_errno.'): '.$err."\n";
		} else {
			$array_result['from_smsh'] = true;
			$array_result['smsh_response_status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$array_result['smsh_response'] = $result;
		}
		curl_close($ch);
		
		return $array_result;
	}

	private function parseResponse($arr, $type) {
		
		$response_obj = new stdClass();
		$response_obj->response = $arr;
		$response_obj->errorCode = 1;
		$response_obj->errorMsg = null;
		$response_obj->userCredit = null;

		if ($response_obj->response['from_smsh'] && stripos($response_obj->response['smsh_response'], 'OK') !== false) {
			//success
			$response_obj->errorCode = 0;
		} else {
			//error
			$response_obj->errorMsg = $response_obj->response['smsh_response'];
			$this->log .= '<pre>'.print_r($response_obj, true)."</pre>\n\n";
			return false;
		}

		if ($type == 'GET_MONEY' && $response_obj->errorCode == 0) {
			$response_obj->userCredit = trim(str_replace('OK MONEY', '', $arr['smsh_response']));
		}
		
		$this->log .= '<pre>'.print_r($response_obj, true)."</pre>\n\n";
		
		return $response_obj;
	}
	
	///// UTILS /////
	
	public function getLog() {
		return $this->log;
	}

	public function validateResponse($response_obj) {
		return (is_object($response_obj) && $response_obj->errorCode != 0);
	}
		
}

