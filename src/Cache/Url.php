<?php

namespace Katu\Cache;

class Url extends \Katu\Cache {

	protected $url;

	protected $curlTimeout = 5;
	protected $curlConnectTimeout = 5;

	public function setUrl($url) {
		$this->url = $url;
		$this->setName(static::generateNameFromUrl($this->url));

		return $this;
	}

	static function generateNameFromUrl($url) {
		$name = [];
		$urlParts = $url->getParts();

		$name = [
			'url',
			$urlParts['scheme'],
			$urlParts['host'],
			$urlParts['path'],
		];

		ksort($urlParts['query']);

		foreach ($urlParts['query'] as $key => $value) {
			$name[] = (new \Katu\Types\TString($key))->getForUrl();
			$name[] = (new \Katu\Types\TString($value))->getForUrl();
		}

		$name = array_values(array_filter($name));

		return $name;
	}

	public function getUrl() {
		return $this->url;
	}

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
				throw new \Katu\Exceptions\CacheCallbackException;
			}
			if ($curl->errorCode) {
				throw new \Katu\Exceptions\CacheCallbackException;
			}

			$curlInfo = $curl->getInfo();
			if (!isset($curlInfo['http_code'])) {
				throw new \Katu\Exceptions\CacheCallbackException;
			}
			if ($curlInfo['http_code'] != 200) {
				throw new \Katu\Exceptions\CacheCallbackException;
			}

			return $src;

		};
	}

	/*****************************************************************************
	 * Code sugar.
	 */

	static function get() {
		$args = func_get_args();

		$url = new \Katu\Types\TUrl((string)$args[0]);

		$object = new static;
		$object->setUrl($args[0]);

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