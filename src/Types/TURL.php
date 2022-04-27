<?php

namespace Katu\Types;

class TURL
{
	const DEFAULT_SCHEME = "http";

	public $value;

	public function __construct($value)
	{
		// Is already a URL object.
		if ($value instanceof static) {
			$this->value = (string)$value;
		// Is just a string.
		} else {
			if (!static::isValid($value)) {
				throw new \Exception("Invalid URL '{$value}'.");
			}

			$this->value = (string)trim($value);
		}
	}

	public function __toString(): string
	{
		return (string)$this->value;
	}

	public static function validate(\Katu\Tools\Validation\Param $param): \Katu\Tools\Validation\Result
	{
		$result = new \Katu\Tools\Validation\Result;

		if (static::isValid($param->getInput())) {
			$result[] = $param->setOutput($param->getInput());
			$result->setResponse($param->getOutput());
		} else {
			$result->addError((new \Katu\Errors\Error("Invalid URL."))->addParam($param));
		}

		return $result;
	}

	public static function make($url, $params = []): TURL
	{
		$params = array_filter((array)$params, function ($i) {
			return mb_strlen($i);
		});

		return new static($url . ($params ? ("?" . http_build_query($params)): null));
	}

	public static function build($parts): TURL
	{
		$url = "";

		if (!isset($parts["host"])) {
			throw new \Exception("Missing host.");
		}

		if (!isset($parts["scheme"])) {
			$url .= static::DEFAULT_SCHEME;
		} else {
			$url .= $parts["scheme"];
		}

		$url .= "://" . $parts["host"];

		if (isset($parts["path"])) {
			$url .= $parts["path"];
		}

		if (isset($parts["query"]) && $parts["query"]) {
			$url .= "?" . http_build_query($parts["query"]);
		}

		return new static($url);
	}

	public static function isValid(string $value): bool
	{
		return filter_var(trim($value), FILTER_VALIDATE_URL) !== false;
	}

	public static function makeValid($value): string
	{
		$url = trim($value);
		if (!$url) {
			return false;
		}

		if (!preg_match("/^https?\:\/\//", $value)) {
			$url = "http://" . $value;
		}

		return $url;
	}

	public function getScheme(): string
	{
		$parts = $this->getParts();

		return $parts["scheme"];
	}

	public function getHost(): string
	{
		$parts = $this->getParts();

		return $parts["host"];
	}

	public function getHostWithScheme(): string
	{
		$parts = $this->getParts();

		return $parts["scheme"] . "://" . $parts["host"];
	}

	public function get2ndLevelDomain(): string
	{
		$parsed = parse_url($this->value);
		if (!isset($parsed["host"])) {
			throw new \Katu\Exceptions\InputErrorException("Invalid URL host.");
		}

		return implode(".", array_slice(explode(".", $parsed["host"]), -2));
	}

	public function getParts(): array
	{
		$parts = parse_url($this->value);

		if (!isset($parts["path"])) {
			$parts["path"] = null;
		}

		if (isset($parts["query"])) {
			parse_str($parts["query"], $query_params);
			$parts["query"] = (array)$query_params;
		} else {
			$parts["query"] = [];
		}

		return $parts;
	}

	public function addQueryParam($name, $value, $overwrite = true): TURL
	{
		$parts = $this->getParts();

		if (!$overwrite && isset($parts["query"][$name])) {
			throw new \Exception("Query param already exists.");
		}

		$parts["query"][$name] = $value;

		$this->value = static::build($parts);

		return $this;
	}

	public function removeQueryParam($name): TURL
	{
		$parts = $this->getParts();

		unset($parts["query"][$name]);

		$this->value = static::build($parts);

		return $this;
	}

	public function getQueryParams()
	{
		$parts = $this->getParts();

		if (isset($parts["query"])) {
			return $parts["query"];
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

	public function getWithoutQuery(): TURL
	{
		$parts = $this->getParts();
		unset($parts["query"]);

		return static::build($parts);
	}

	public function getWithoutTrailingIndex(): TURL
	{
		$parts = $this->getParts();

		if (isset($parts["path"])) {
			$pathinfo = pathinfo($parts["path"]);
			if (preg_match("/^index\.(php|htm|html)$/", $pathinfo["basename"])) {
				$parts["path"] = $pathinfo["dirname"];
			}
		}

		$this->value = static::build($parts);

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
			if (in_array($curl->httpStatusCode, [301, 302]) && isset($curl->responseHeaders["Location"])) {
				$url = new static($curl->responseHeaders["Location"]);
			} else {
				return $response;
			}
		}
	}

	public function getAsTemporaryFile()
	{
		$basename = (new \Katu\Files\File($this))->getBasename();
		$tmpFile = new \Katu\Files\File(\App\App::getTemporaryDir(), "url", \Katu\Tools\Random\Generator::getFileName(), $basename);
		$tmpFile->set($this->get());

		return $tmpFile;
	}

	public function getPingExec(string $method = "GET", \Katu\Models\Presets\User $user = null)
	{
		return (new \Katu\Tools\Curl\Exec($this))
			->setMethod($method)
			->setUser($user)
			;
	}

	public function ping(string $method = "GET", \Katu\Models\Presets\User $user = null)
	{
		return $this->getPingExec($method, $user)->exec();
	}
}
