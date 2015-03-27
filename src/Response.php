<?php
// vim: sw=4:ts=4:noet:sta:

namespace curl;

/**
 * Curl response object
 *
 * @property string $contentType
 */
class Response extends \yii\base\Object {
	/**
	 * @var integer http code
	 */
	public $code = 0;

	/**
	 * @var array headers (name => value)
	 */
	public $headers = [ ];

	/**
	 * @var string data
	 */
	public $data = '';

	public function __construct($httpCode, $response, $headerSize) {
		$this->code = $httpCode;
		$this->headers = $this->parseHeaders(substr($response, 0, $headerSize));
		$this->data = substr($response, $headerSize);
	}

	public function getContentType() {
		return isset($this->headers['Content-Type']) ? explode(';', $this->headers['Content-Type'])[0] : '';
	}

	private function parseHeaders($str) {
		$lines = explode("\r\n", trim($str));
		unset($lines[0]);
		$result = [ ];
		foreach ($lines as $line) {
			if (strpos($line, ':')) {
				list($k, $v) = explode(": ", $line);
				$result[$k] = $v;
			}
		}
		return $result;
	}

	public function __toString() {
		return $this->data;
	}
}
