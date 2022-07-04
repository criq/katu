<?php

namespace Katu\Cache;

use Katu\Tools\Calendar\Timeout;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;

class URL
{
	protected $curl;
	protected $timeout;
	protected $url;

	public function __construct(TURL $url, Timeout $timeout, ?\Curl\Curl $curl = null)
	{
		$this->setURL($url);
		$this->setTimeout($timeout);
		$this->setCurl($curl);
	}

	public function setURL(TURL $url): URL
	{
		$this->url = $url;

		return $this;
	}

	public function getURL(): TURL
	{
		return $this->url;
	}

	public function setTimeout(Timeout $timeout): URL
	{
		$this->timeout = $timeout;

		return $this;
	}

	public function getTimeout(): Timeout
	{
		return $this->timeout;
	}

	public function setCurl(?\Curl\Curl $curl = null): URL
	{
		$this->curl = $curl;

		return $this;
	}

	public function getCurl(): \Curl\Curl
	{
		if ($this->curl) {
			return $this->curl;
		}

		$curl = new \Curl\Curl;
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);

		return $curl;
	}

	public function getCallback(): callable
	{
		return function () {
			$curl = $this->getCurl();
			$src = $curl->get((string)$this->getURL());

			if (!isset($curl->errorCode)) {
				throw new \Katu\Exceptions\CacheCallbackException("Error fetching URL \"{$this->getURL()}\".");
			}

			if ($curl->errorCode) {
				throw new \Katu\Exceptions\CacheCallbackException("Error fetching URL \"{$this->getURL()}\", error code {$curl->errorCode}, error \"{$curl->error}\".");
			}

			$curlInfo = $curl->getInfo();
			if (!isset($curlInfo["http_code"])) {
				throw new \Katu\Exceptions\CacheCallbackException;
			}

			if ($curlInfo["http_code"] != 200) {
				throw new \Katu\Exceptions\CacheCallbackException;
			}

			return $src;
		};
	}

	public function getGeneralCache(): General
	{
		$urlCache = new static($this->getURL(), $this->getTimeout(), $this->getCurl());

		return new General(new TIdentifier("url", $this->getURL()), $this->getTimeout(), $urlCache->getCallback());
	}

	public static function get(TURL $url, Timeout $timeout, ?\Curl\Curl $curl = null)
	{
		$urlCache = new static($url, $timeout, $curl);
		$cache = new General(new TIdentifier("url", $url), $timeout, $urlCache->getCallback());

		return $cache->getResult();
	}
}
