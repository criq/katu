<?php

namespace Katu\Cache;

class Url extends \Katu\Cache
{
	protected $curlConnectTimeout = 5;
	protected $curlEncoding = null;
	protected $curlTimeout = 5;
	protected $url;

	public function __construct($url = null, $timeout = null)
	{
		$this->setUrl($url);
		$this->setTimeout($timeout);
		$this->setCallback($this->generateCallback());
		$this->setArgs($url);
	}

	public function setUrl($url)
	{
		$this->url = $url;
		$this->setName(static::generateNameFromUrl($this->url));

		return $this;
	}

	public static function generateNameFromUrl($url)
	{
		$name = [];

		$url = new \Katu\Types\TUrl((string)$url);
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

	public function getUrl()
	{
		return $this->url;
	}

	public function setCurlTimeout($curlTimeout)
	{
		$this->curlTimeout = $curlTimeout;

		return $this;
	}

	public function getCurlTimeout()
	{
		return $this->curlTimeout;
	}

	public function setCurlConnectTimeout($curlConnectTimeout)
	{
		$this->curlConnectTimeout = $curlConnectTimeout;

		return $this;
	}

	public function getCurlConnectTimeout()
	{
		return $this->curlConnectTimeout;
	}

	public function setCurlEncoding($curlEncoding)
	{
		$this->curlEncoding = $curlEncoding;

		return $this;
	}

	public function getCurlEncoding()
	{
		return $this->curlEncoding;
	}

	public function generateCallback()
	{
		return function ($url) {
			$curl = new \Curl\Curl;

			try {
				$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
			} catch (\Exception $e) {
				// Nevermind.
			}

			$curl->setTimeout($this->curlTimeout);
			$curl->setConnectTimeout($this->curlConnectTimeout);

			if ($this->curlEncoding) {
				$curl->setOpt(CURLOPT_ENCODING, $this->curlEncoding);
			}

			$src = $curl->get((string)$url);

			if (!isset($curl->errorCode)) {
				throw new \Katu\Exceptions\CacheCallbackException(strtr("Error fetching URL %url%.", [
					'url' => (string)$url,
				]));
			}
			if ($curl->errorCode) {
				throw new \Katu\Exceptions\CacheCallbackException(strtr("Error fetching URL %url%, error code %errorCode%, error %error%.", [
					'%url%' => (string)$url,
					'%errorCode%' => $curl->errorCode,
					'%error%' => $curl->error,
				]));
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

	public static function get()
	{
		$args = func_get_args();

		$object = new static((string)$args[0]);
		if (isset($args[1])) {
			$object->setTimeout($args[1]);
		}

		return $object->getResult();
	}
}
