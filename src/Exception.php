<?php
// vim: sw=4:ts=4:noet:sta:

namespace curl;

/**
 * curl exception
 */
class Exception extends \yii\base\Exception {
	public $httpCode;

	public $response;

	public function __construct($msg, $errno, $httpCode = null, $response = null) {
		parent::__construct($msg, $errno);
		$this->httpCode = $httpCode;
		$this->response = $response;
	}
}
