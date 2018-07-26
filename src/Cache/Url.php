<?php

namespace Katu\Cache;

class Url extends \Katu\Cache {

	private $curlTimeout = 5;
	private $curlConnectTimeout = 5;

	public function setCurlTimeout($curlTimeout) {
		$this->curlTimeout = $curlTimeout;

		return $this;
	}

	public function getCurlTimeout() {
		return $this->curlTimeout;
	}

	public function setCurlConnectTimeout($curlConnectTimeout) {
		$this->curlConnectTimeout = $curlConnectTimeout;

		return $this;
	}

	public function getCurlConnectTimeout() {
		return $this->curlConnectTimeout;
	}

	public function generateCallback() {
		return function($url, $options = []) {

			$curl = new \Curl\Curl;
			$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
			if (isset($options['curlTimeout'])) {
				$curl->setTimeout($options['curlTimeout']);
			}
			if (isset($options['curlConnectTimeout'])) {
				$curl->setConnectTimeout($options['curlConnectTimeout']);
			}

			$src = $curl->get((string)$url);

			if (!isset($curl->errorCode)) {
				throw new \Katu\Exceptions\DoNotCacheException;
			}
			if ($curl->errorCode) {
				throw new \Katu\Exceptions\DoNotCacheException;
			}

			$curlInfo = $curl->getInfo();
			if (!isset($curlInfo['http_code'])) {
				throw new \Katu\Exceptions\DoNotCacheException;
			}
			if ($curlInfo['http_code'] != 200) {
				throw new \Katu\Exceptions\DoNotCacheException;
			}

			return $src;

		};
	}

	/*****************************************************************************
	 * Code sugar.
	 */

	static function get() {
		$args = func_get_args();

		$object = new static(['url', (string)$args[0]]);

		if (isset($args[1])) {
			$object->setTimeout($args[1]);
		}

		$object->setCallback($object->generateCallback());

		$object->setArgs((string)$args[0], [
			'curlTimeout' => $object->getCurlTimeout(),
			'curlConnectTimeout' => $object->getCurlConnectTimeout(),
		]);

		return $object->getResult();
	}

}
