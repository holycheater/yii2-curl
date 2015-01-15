<?php
// vim: sw=4:ts=4:noet:sta:
namespace curl;

use curl\Exception as CurlException;

/**
 * Curl request
 */
class Request {
	public $url;

	public $timeout = 2;

	public $data;

	/**
	 * HTTP request type (GET/PUT/POST/DELETE)
	 */
	public $requestType = 'GET';

	/**
	 * @var string http authentication username
	 */
	public $username;

	/**
	 * @var string http authentication password
	 */
	public $password;

	/**
	 * http request encoding
	 */
	public $encoding = 'gzip';

	/**
	 * @var array http-headers ( name => value )
	 */
	public $headers = [ ];

	protected $ch;

	/**
	 * curl options
	 */
	public $options = [ ];

	public function __construct($url = null) {
		$this->url = $url;
	}

	/**
	 * exec curl request
	 * @return CurlResponse response
	 * @throws CurlException on tranfser error or http code >= 400
	 */
	public function exec() {
		$this->ch = curl_init();
		curl_setopt_array($this->ch, [
			CURLOPT_URL => $this->url,
			CURLOPT_POSTFIELDS => $this->data,
			CURLOPT_CUSTOMREQUEST => $this->requestType,
			CURLOPT_HTTPHEADER => $this->createHeaders(),
			CURLOPT_TIMEOUT_MS => intval($this->timeout * 1000),
			CURLOPT_ENCODING => $this->encoding,
			CURLOPT_FAILONERROR => false,
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
		]);
		curl_setopt_array($this->ch, $this->options);

		if ($this->username && $this->password) {
			curl_setopt($this->ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		}

		if ($this->data !== null)
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data);

		$resultData = curl_exec($this->ch);
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

		if (curl_errno($this->ch) !== CURLE_OK) {
			throw new CurlException(curl_error($this->ch), curl_errno($this->ch));
		}

		$response = new Response($httpCode, $resultData, curl_getinfo($this->ch, CURLINFO_HEADER_SIZE));

		if  ($httpCode >= 400) {
			throw new CurlException("HTTP transfer error {$httpCode}", null, $httpCode, $response);
		}

		curl_close($this->ch);

		return $response;
	}

	/**
	 * send POST query
	 */
	public function post($data) {
		$this->requestType = 'POST';
		$this->data = $data;
		return $this->exec();
	}

	/**
	 * send HEAD request
	 */
	public function head() {
		$this->extraOpts[CURLOPT_NOBODY] = true;
		return $this->exec();
	}

	protected function createHeaders() {
		$result = [ ];
		foreach ($this->headers as $k => $v) {
			$result[] = "{$k}: {$v}";
		}
		return $result;
	}
}
