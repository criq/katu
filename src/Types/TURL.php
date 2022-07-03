<?php

namespace Katu\Types;

use Katu\Models\Presets\User;

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

	public static function validate(\Katu\Tools\Validation\Param $param): \Katu\Tools\Validation\Validation
	{
		$result = new \Katu\Tools\Validation\Validation;

		if (static::isValid($param->getInput())) {
			$result[] = $param->setOutput($param->getInput());
			$result->setResponse($param->getOutput());
		} else {
			$result->addError((new \Katu\Errors\Error("Invalid URL."))->addParam($param));
		}

		return $result;
	}

	public static function make($url, array $params = []): TURL
	{
		$queryParams = trim(http_build_query($params));

		return new static($url . ($queryParams ? ("?" . $queryParams) : null));
	}

	public static function build(array $parts): TURL
	{
		$url = "";

		if (!($parts["host"] ?? null)) {
			throw new \Exception("Missing host.");
		}

		if (!($parts["scheme"] ?? null)) {
			$url .= static::DEFAULT_SCHEME;
		} else {
			$url .= $parts["scheme"];
		}

		$url .= "://" . $parts["host"];

		if ($parts["path"] ?? null) {
			$url .= $parts["path"];
		}

		if ($parts["query"] ?? null) {
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
		if (!($parsed["host"] ?? null)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid URL host.");
		}

		return implode(".", array_slice(explode(".", $parsed["host"]), -2));
	}

	public function getParts(): array
	{
		$parts = parse_url($this->value);

		if (!($parts["path"] ?? null)) {
			$parts["path"] = null;
		}

		if ($parts["query"] ?? null) {
			parse_str($parts["query"], $query_params);
			$parts["query"] = (array)$query_params;
		} else {
			$parts["query"] = [];
		}

		return $parts;
	}

	public function addQueryParam(string $name, $value, $overwrite = true): TURL
	{
		$parts = $this->getParts();

		if (!$overwrite && ($parts["query"][$name] ?? null)) {
			throw new \Exception("Query param already exists.");
		}

		$parts["query"][$name] = $value;

		$this->value = static::build($parts);

		return $this;
	}

	public function removeQueryParam(string $name): TURL
	{
		$parts = $this->getParts();

		unset($parts["query"][$name]);

		$this->value = static::build($parts);

		return $this;
	}

	public function getQueryParams()
	{
		$parts = $this->getParts();

		if ($parts["query"] ?? null) {
			return $parts["query"];
		}

		return null;
	}

	public function getQueryParam(string $name)
	{
		$params = $this->getQueryParams();

		if ($params[$name] ?? null) {
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

		if ($parts["path"] ?? null) {
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
			if (in_array($curl->httpStatusCode, [301, 302]) && ($curl->responseHeaders["Location"] ?? null)) {
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

	public function getPingExec(string $method = "GET", ?User $user = null)
	{
		return (new \Katu\Tools\Curl\Exec($this))
			->setMethod($method)
			->setUser($user)
			;
	}

	public function ping(string $method = "GET", ?User $user = null)
	{
		return $this->getPingExec($method, $user)->exec();
	}
}
