<?php

namespace Katu\Tools\Views;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class TwigEngine implements ViewEngineInterface
{
	protected $request;
	protected $twig;

	abstract protected static function getTwigLoader(): \Twig\Loader\LoaderInterface;

	public function __construct(?ServerRequestInterface $request = null)
	{
		$this->setRequest($request);
		$this->setTwig($this->createTwig());
	}

	public function setRequest(?ServerRequestInterface $request): TwigEngine
	{
		$this->request = $request;

		return $this;
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request ?: \App\App::get()->getContainer()->get("request");
	}

	protected static function createTwig(): \Twig\Environment
	{
		$twig = new \Twig\Environment(static::getTwigLoader(), static::getTwigConfig());

		/***************************************************************************
		 * Image.
		 */
		$twig->addFunction(new \Twig\TwigFunction("getImage", function ($uri) {
			try {
				return new \Katu\Tools\Images\Image($uri);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		/***************************************************************************
		 * Text.
		 */
		$twig->addFilter(new \Twig\TwigFilter("shorten", function ($string, $length, $options = []) {
			$string = trim($string);
			$shorter = trim(mb_substr($string, 0, $length));
			if (mb_strlen($shorter) < mb_strlen($string) && ($options["append"] ?? null)) {
				$shorter .= $options["append"];
			}

			return new \Twig\Markup($shorter, "UTF-8");
		}));

		$twig->addFilter(new \Twig\TwigFilter("shortenUrl", function ($string, $length, $options = []) {
			$sanitized = rtrim(preg_replace("/^https?\:\/\//", "", $string), "/?");
			$shorter = rtrim(substr($sanitized, 0, $length), "/?");
			if (strlen($shorter) < strlen($sanitized)) {
				$shorter .= "...";
			}

			return $shorter;
		}));

		$twig->addFilter(new \Twig\TwigFilter("asArray", function ($variable) {
			return (array) $variable;
		}));

		$twig->addFilter(new \Twig\TwigFilter("unique", function ($variable) {
			return array_unique($variable);
		}));

		$twig->addFilter(new \Twig\TwigFilter("joinInSentence", function ($list, $delimiter, $lastDelimiter) {
			return (new \Katu\Types\TArray($list))->implodeInSentence($delimiter, $lastDelimiter);
		}));

		$twig->addFilter(new \Twig\TwigFilter("isValidDateTime", function ($date) {
			try {
				return (new \Katu\Tools\Calendar\Time($date))->isValid();
			} catch (\Throwable $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig\TwigFilter("markdown", function ($text) {
			return \Michelf\Markdown::defaultTransform($text);
		}));

		$twig->addFilter(new \Twig\TwigFilter("nbsp", function ($text) {
			$text = preg_replace("/\b([aiouksvz])(\s)/i", "\\1&nbsp;", $text);
			$text = preg_replace("/([0-9])\s+(%)/i", "\\1&nbsp;\\2", $text);

			return new \Twig\Markup($text, "UTF-8");
		}));

		$twig->addFilter(new \Twig\TwigFilter("str", function ($value) {
			return (string)$value;
		}));

		/***************************************************************************
		 * Functions.
		 */
		$twig->addFunction(new \Twig\TwigFunction("dump", function () {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig\TwigFunction("getTimeout", function ($timeout) {
			return new \Katu\Tools\Calendar\Timeout($timeout);
		}));

		$twig->addFunction(new \Twig\TwigFunction("getVersion", function () {
			return \Katu\Config\Env::getVersion();
		}));

		$twig->addFunction(new \Twig\TwigFunction("getBaseDir", function () {
			return \App\App::getBaseDir();
		}));

		// Deprecated.
		$twig->addFunction(new \Twig\TwigFunction("geTURLFor", function () {
			return (string) call_user_func_array(["\Katu\Tools\Routing\URL", "getFor"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("url", function () {
			return (string) call_user_func_array(["\Katu\Tools\Routing\URL", "getFor"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("urlDecoded", function () {
			return (string) call_user_func_array(["\Katu\Tools\Routing\URL", "getDecodedFor"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("getCurrentURL", function () {
			return (string) call_user_func_array(["\Katu\Tools\Routing\URL", "getCurrent"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("makeUrl", function () {
			return (string) call_user_func_array(["\Katu\Types\TURL", "make"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("getConfig", function () {
			return call_user_func_array(["\Katu\Config", "get"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("getCookie", function () {
			return call_user_func_array(["\Katu\Tools\Cookies\Cookie", "get"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("getSession", function () {
			return call_user_func_array(["\Katu\Tools\Session\Session", "get"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("getFlash", function () {
			return call_user_func_array(["\Katu\Tools\Session\Flash", "get"], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction("getCsrfToken", function () {
			return \Katu\Tools\Forms\Token::getFreshToken();
		}));

		$twig->addFunction(new \Twig\TwigFunction("getFile", function () {
			$args = array_merge([\App\App::getBaseDir()], func_get_args());
			$path = \Katu\Files\File::joinPaths(...$args);

			return new \Katu\Files\File($path);
		}));

		$twig->addFunction(new \Twig\TwigFunction("getHashedFile", function ($path) {
			try {
				$hashedFiles = \Katu\Files\File::getHashedFiles(\App\App::getBaseDir(), ...func_get_args());

				return $hashedFiles[0] ?? null;
			} catch (\Throwable $e) {
				return null;
			}
		}));

		$twig->addFunction(new \Twig\TwigFunction("lipsum", function ($sentences = 1) {
			try {
				return implode(" ", \Katu\Tools\Random\LoremIpsum\Blabot::getSentences($sentences)->getArray());
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				return implode(" ", \Katu\Tools\Random\LoremIpsum\FillText::getSentences($sentences)->getArray());
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				return implode(" ", \Katu\Tools\Random\LoremIpsum\BaconIpsum::getSentences($sentences)->getArray());
			} catch (\Throwable $e) {
				// Nevermind.
			}

			return false;
		}));

		return $twig;
	}

	protected function setTwig(\Twig\Environment $twig): TwigEngine
	{
		$this->twig = $twig;

		return $this;
	}

	protected function getTwig(): \Twig\Environment
	{
		return $this->twig;
	}

	protected static function getTwigConfig(): array
	{
		return [
			"auto_reload" => false,
			"cache" => (string)\Katu\Files\File::joinPaths(\App\App::getTemporaryDir(), "twig", \Katu\Config\Env::getVersion()),
			"debug" => false,
			"optimizations" => -1,
			"strict_variables" => false,
		];
	}

	protected function getCommonData(): array
	{
		$data["_site"]["baseDir"] = \App\App::getBaseDir();
		$data["_site"]["baseUrl"] = \Katu\Config\Config::get("app", "baseUrl");

		try {
			$data["_site"]["apiUrl"] = \Katu\Config\Config::get("app", "apiUrl");
		} catch (\Throwable $e) {
			// Doesn"t exist.
		}

		try {
			$data["_site"]["timezone"] = \Katu\Config\Config::get("app", "timezone");
		} catch (\Throwable $e) {
			// Doesn"t exist.
		}

		try {
			$data["_request"]["uri"] = (string)$this->getRequest()->getUri();
		} catch (\Throwable $e) {
			// Doesn"t exist.
		}

		try {
			$data["_request"]["url"] = (string)\Katu\Tools\Routing\URL::getCurrent();
		} catch (\Throwable $e) {
			// Doesn"t exist.
		}

		try {
			$data["_request"]["ip"] = (string)$this->getRequest()->getServerParams()["REMOTE_ADDR"];
		} catch (\Throwable $e) {
			// Doesn"t exist.
		}

		try {
			$data["_request"]["params"] = (array)$this->getRequest()->getQueryParams();
		} catch (\Throwable $e) {
			// Doesn"t exist.
		}

		try {
			if ($this->getRequest()->getAttribute("route")) {
				$data["_request"]["route"] = (array)[
					"pattern" => $this->getRequest()->getAttribute("route")->getPattern(),
					"name" => $this->getRequest()->getAttribute("route")->getName(),
					"params" => $this->getRequest()->getAttribute("route")->getArguments(),
				];
			}
		} catch (\Throwable $e) {

		}

		$data["_agent"] = new \Jenssegers\Agent\Agent();

		// User.
		$userClass = \App\App::getUserModelClass();
		if ($userClass->exists() && $userClass->getName()::hasConnection()) {
			$userClassName = $userClass->getName();
			$data["_user"] = $userClassName::getFromRequest($this->getRequest());
		}

		// Settings.
		$settingClass = \App\App::getSettingModelClass();
		if ($settingClass->exists() && $settingClass->getName()::hasConnection()) {
			$data["_settings"] = $settingClass->getName()::getAllAsAssoc();
		}

		$data["_platform"] = \Katu\Config\Env::getPlatform();
		$data["_config"] = \Katu\Config\Config::get();
		$data["_session"] = \Katu\Tools\Session\Session::get();
		$data["_flash"] = \Katu\Tools\Session\Flash::get();
		$data["_cookies"] = \Katu\Tools\Cookies\Cookie::get();
		$data["_upload"] = [
			"maxSize" => \Katu\Files\Upload::getMaxSize()->getInB(),
		];

		return $data;
	}

	public function render(string $template, array $data = []): StreamInterface
	{
		$twig = $this->getTwig();
		$data = array_merge_recursive($this->getCommonData($this->getRequest()), $data);

		return \GuzzleHttp\Psr7\Utils::streamFor(trim($twig->render($template, $data)));
	}
}
