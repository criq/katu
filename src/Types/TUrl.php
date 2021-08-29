<?php

namespace Katu\Types;

class TUrl
{
	const DEFAULT_SCHEME = 'http';

	public $value;

	public function __construct($value)
	{
		// Remove invalid characters.
		$value = iconv(mb_detect_encoding($value), 'ASCII//IGNORE', $value);

		if (!self::isValid($value)) {
			throw new \Exception("Invalid URL '" . $value . "'.");
		}

		$this->value = (string)trim($value);
	}

	public function __toString()
	{
		return $this->value;
	}

	public static function make($url, $params = [])
	{
		$params = array_filter((array)$params, function ($i) {
			if (is_string($i)) {
				return strlen($i);
			}
			return $i;
		});

		return new self($url . ($params ? ('?' . http_build_query($params)) : null));
	}

	public static function build($parts)
	{
		$url = '';

		if (!isset($parts['host'])) {
			throw new \Exception("Missing host");
		}

		if (!isset($parts['scheme'])) {
			$url .= self::DEFAULT_SCHEME;
		} else {
			$url .= $parts['scheme'];
		}

		$url .= '://' . $parts['host'];

		if (isset($parts['path'])) {
			$url .= $parts['path'];
		}

		if (isset($parts['query']) && $parts['query']) {
			$url .= '?' . http_build_query($parts['query']);
		}

		return $url;
	}

	public static function isValid($value)
	{
		return filter_var(trim($value), FILTER_VALIDATE_URL) !== false;
	}

	public static function makeValid($value)
	{
		$url = trim($value);
		if (!$url) {
			return false;
		}

		if (!preg_match('/^https?\:\/\//', $value)) {
			$url = 'http://' . $value;
		}

		return $url;
	}

	public function getScheme()
	{
		$parts = $this->getParts();

		return $parts['scheme'];
	}

	public function getHost()
	{
		$parts = $this->getParts();

		return $parts['host'];
	}

	public function getHostWithScheme()
	{
		$parts = $this->getParts();

		return $parts['scheme'] . '://' . $parts['host'];
	}

	public function get2ndLevelDomain()
	{
		$parsed = parse_url($this->value);
		if (!isset($parsed['host'])) {
			throw new \Katu\Exceptions\Exception("Invalid URL host.");
		}

		return implode('.', array_slice(explode('.', $parsed['host']), -2));
	}

	public function getParts()
	{
		$parts = parse_url($this->value);

		if (!isset($parts['path'])) {
			$parts['path'] = null;
		}

		if (isset($parts['query'])) {
			parse_str($parts['query'], $query_params);
			$parts['query'] = (array)$query_params;
		} else {
			$parts['query'] = [];
		}

		return $parts;
	}

	public function addQueryParam($name, $value, $overwrite = true)
	{
		$parts = $this->getParts();

		if (!$overwrite && isset($parts['query'][$name])) {
			throw new \Exception("Query param already exists.");
		}

		$parts['query'][$name] = $value;

		$this->value = self::build($parts);

		return $this;
	}

	public function removeQueryParam($name)
	{
		$parts = $this->getParts();

		unset($parts['query'][$name]);

		$this->value = self::build($parts);

		return $this;
	}

	public function getQueryParams()
	{
		$parts = $this->getParts();

		if (isset($parts['query'])) {
			return $parts['query'];
		}

		return null;
	}

	public function getQueryParam($name)
	{
		$params = $this->getQueryParams();

		if (isset($params[$name])) {
			return $params[$name];
		}

		return null;
	}

	public function getWithoutQuery()
	{
		$parts = $this->getParts();

		unset($parts['query']);

		$this->value = self::build($parts);

		return $this;
	}

	public function getWithoutTrailingIndex()
	{
		$parts = $this->getParts();

		if (isset($parts['path'])) {
			$pathinfo = pathinfo($parts['path']);
			if (preg_match('/^index\.(php|htm|html)$/', $pathinfo['basename'])) {
				$parts['path'] = $pathinfo['dirname'];
			}
		}

		$this->value = self::build($parts);

		return $this;
	}

	public function get(&$curl = null)
	{
		$url = $this;

		if (is_null($curl)) {
			$curl = new \Curl\Curl;
		}

		try {
			$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		} catch (\ErrorException $e) {
			// Nothing to do, open_basedir is probably set.
		}

		// Bypass disabled CURLOPT_FOLLOWLOCATION.
		while ($url) {
			$response = $curl->get($url);
			if (in_array($curl->httpStatusCode, [301, 302]) && isset($curl->responseHeaders['Location'])) {
				$url = new static($curl->responseHeaders['Location']);
			} else {
				return $response;
			}
		}
	}

	public function getAsTemporaryFile()
	{
		$basename = (new \Katu\Utils\File($this))->getBasename();
		$tmpFile = new \Katu\Utils\File(TMP_PATH, 'url', \Katu\Utils\Random::getFileName(), $basename);
		$tmpFile->set($this->get());

		return $tmpFile;
	}

	public function ping($timeout = 1)
	{
		$curl = new \Curl\Curl;

		try {
			$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		} catch (\ErrorException $e) {
			// Nothing to do, open_basedir is probably set.
		}

		$curl->setOpt(CURLOPT_TIMEOUT, $timeout);

		return $curl->get((string)$this);
	}
}
