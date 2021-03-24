<?php

namespace Katu\Views;

class View
{
	public static function getTwig()
	{
		return new \Twig\Environment(static::getTwigLoader(), static::getTwigConfig());
	}

	public static function getTwigConfig()
	{
		return [
			'auto_reload' => false,
			'cache' => (string)\Katu\Files\File::joinPaths(\Katu\App::getTemporaryDir(), 'twig', \Katu\Config\Env::getVersion()),
			'debug' => false,
			'optimizations' => -1,
			'strict_variables' => false,
		];
	}

	public static function getTwigLoader()
	{
		return new \Twig\Loader\FilesystemLoader(static::getTwigDirs());
	}

	public static function getTwigDirs()
	{
		$dirs = [];
		if (!isset($dirs) || (isset($dirs) && !$dirs)) {
			$dirs = [
				new \Katu\Files\File(\Katu\App::getBaseDir(), 'app', 'Views'),
				new \Katu\Files\File(\Katu\Tools\Services\Composer\Composer::getDir(), substr(__DIR__, strcmp(\Katu\Tools\Services\Composer\Composer::getDir(), __DIR__))),
			];

			$dirs = array_unique(array_filter(array_map(function ($dir) {
				return $dir->exists() ? $dir->getPath() : null;
			}, $dirs)));
		}

		return $dirs;
	}

	public static function extendTwig(&$twig)
	{

		/***************************************************************************
		 * Image.
		 */
		$twig->addFunction(new \Twig\TwigFunction('getImage', function ($uri) {
			try {
				return new \Katu\Tools\Images\Image($uri);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		/***************************************************************************
		 * Text.
		 */
		$twig->addFilter(new \Twig\TwigFilter('shorten', function ($string, $length, $options = []) {
			$string = trim($string);
			$shorter = trim(mb_substr($string, 0, $length));
			if (mb_strlen($shorter) < mb_strlen($string) && ($options['append'] ?? null)) {
				$shorter .= $options['append'];
			}

			return new \Twig\Markup($shorter, 'UTF-8');
		}));

		$twig->addFilter(new \Twig\TwigFilter('shortenUrl', function ($string, $length, $options = []) {
			$sanitized = rtrim(preg_replace('/^https?\:\/\//', null, $string), '/?');
			$shorter = rtrim(substr($sanitized, 0, $length), '/?');
			if (strlen($shorter) < strlen($sanitized)) {
				$shorter .= "...";
			}

			return $shorter;
		}));

		$twig->addFilter(new \Twig\TwigFilter('asArray', function ($variable) {
			return (array) $variable;
		}));

		$twig->addFilter(new \Twig\TwigFilter('unique', function ($variable) {
			return array_unique($variable);
		}));

		$twig->addFilter(new \Twig\TwigFilter('joinInSentence', function ($list, $delimiter, $lastDelimiter) {
			return (new \Katu\Types\TArray($list))->implodeInSentence($delimiter, $lastDelimiter);
		}));

		$twig->addFilter(new \Twig\TwigFilter('isValidDateTime', function ($date) {
			try {
				return (new \Katu\Tools\DateTime\DateTime($date))->isValid();
			} catch (\Exception $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig\TwigFilter('markdown', function ($text) {
			return \Michelf\Markdown::defaultTransform($text);
		}));

		$twig->addFilter(new \Twig\TwigFilter('nbsp', function ($text) {
			$text = preg_replace('/\b([aiouksvz])(\s)/i', '\\1&nbsp;', $text);
			$text = preg_replace('/([0-9])\s+(%)/i', '\\1&nbsp;\\2', $text);

			return new \Twig\Markup($text, 'UTF-8');
		}));

		/***************************************************************************
		 * Functions.
		 */
		$twig->addFunction(new \Twig\TwigFunction('dump', function () {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig\TwigFunction('getVersion', function () {
			return \Katu\Config\Env::getVersion();
		}));

		$twig->addFunction(new \Twig\TwigFunction('getBaseDir', function () {
			return \Katu\App::getBaseDir();
		}));

		// Deprecated.
		$twig->addFunction(new \Twig\TwigFunction('geTURLFor', function () {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('url', function () {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('urlDecoded', function () {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getDecodedFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getCurrenTURL', function () {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getCurrent'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('makeUrl', function () {
			return (string) call_user_func_array(['\Katu\Types\TURL', 'make'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getConfig', function () {
			return call_user_func_array(['\Katu\Config', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getCookie', function () {
			return call_user_func_array(['\Katu\Tools\Cookies\Cookie', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getSession', function () {
			return call_user_func_array(['\Katu\Tools\Session\Session', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getFlash', function () {
			return call_user_func_array(['\Katu\Tools\Session\Flash', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getCsrfToken', function () {
			$params = (array) @func_get_arg(0);

			return \Katu\Tools\Security\CSRF::getFreshToken($params);
		}));

		$twig->addFunction(new \Twig\TwigFunction('getFile', function () {
			$args = array_merge([\Katu\App::getBaseDir()], func_get_args());
			$path = \Katu\Files\File::joinPaths(...$args);

			return new \Katu\Files\File($path);
		}));

		$twig->addFunction(new \Twig\TwigFunction('getHashedFile', function ($path) {
			try {
				$placeholderFile = new \Katu\Files\File(\Katu\App::getBaseDir(), $path);
				$platformDir = new \Katu\Files\File(preg_replace('/{platform}/', \Katu\Config\Env::getPlatform(), $placeholderFile->getDir()));

				$fileRegexp = $placeholderFile->getBasename();
				$fileRegexp = preg_replace('/{hash}/', '([0-9a-f]+)?', $fileRegexp);
				$fileRegexp = preg_replace('/{dash}/', '-?', $fileRegexp);
				$fileRegexp = '/^' . $fileRegexp . '$/';

				$matchedFiles = [];
				foreach ($platformDir->getFiles() as $file) {
					if (preg_match($fileRegexp, $file->getBasename())) {
						$matchedFiles[] = $file;
					}
				}

				usort($matchedFiles, function ($a, $b) {
					return filemtime($a) > filemtime($b) ? -1 : 1;
				});

				return $matchedFiles[0] ?? false;
			} catch (\Throwable $e) {
				return false;
			}
		}));

		$twig->addFunction(new \Twig\TwigFunction('lipsum', function ($sentences = 1) {
			try {
				return implode(' ', \Katu\Tools\Random\LoremIpsum\Blabot::getSentences($sentences)->getArray());
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				return implode(' ', \Katu\Tools\Random\LoremIpsum\FillText::getSentences($sentences)->getArray());
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				return implode(' ', \Katu\Tools\Random\LoremIpsum\BaconIpsum::getSentences($sentences)->getArray());
			} catch (\Throwable $e) {
				// Nevermind.
			}

			return false;
		}));

		return true;
	}

	public static function getCommonData(\Slim\Http\Request $request = null, \Slim\Http\Response $response = null, array $args = [])
	{
		$data['_site']['baseDir'] = \Katu\App::getBaseDir();
		$data['_site']['baseUrl'] = \Katu\Config\Config::get('app', 'baseUrl');

		try {
			$data['_site']['apiUrl']  = \Katu\Config\Config::get('app', 'apiUrl');
		} catch (\Throwable $e) {
			/* Doesn't exist. */
		}

		try {
			$data['_site']['timezone'] = \Katu\Config\Config::get('app', 'timezone');
		} catch (\Throwable $e) {
			/* Doesn't exist. */
		}

		try {
			$data['_request']['uri'] = (string)$request->getUri();
		} catch (\Throwable $e) {
			/* Doesn't exist. */
		}

		try {
			$data['_request']['url'] = (string)\Katu\Tools\Routing\URL::getCurrent();
		} catch (\Throwable $e) {
			/* Doesn't exist. */
		}

		try {
			$data['_request']['ip'] = (string)$request->getServerParam('REMOTE_ADDR');
		} catch (\Throwable $e) {
			/* Doesn't exist. */
		}

		try {
			$data['_request']['params'] = (array)$request->getParams();
		} catch (\Throwable $e) {
			/* Doesn't exist. */
		}

		try {
			if ($request->getAttribute('route')) {
				$data['_request']['route']  = (array)[
					'pattern' => $request->getAttribute('route')->getPattern(),
					'name' => $request->getAttribute('route')->getName(),
					'params' => $request->getAttribute('route')->getArguments(),
				];
			}
		} catch (\Throwable $e) {
			// Doesn't exist.
		}

		$data['_agent'] = new \Jenssegers\Agent\Agent();

		if (class_exists("App\Models\User")) {
			$data['_user'] = \App\Models\User::getCurrent();
		}

		if (class_exists("App\Models\Setting")) {
			$data['_settings'] = \App\Models\Setting::getAllAsAssoc();
		}

		$data['_platform'] = \Katu\Config\Env::getPlatform();
		$data['_config']   = \Katu\Config\Config::get();
		$data['_session']  = \Katu\Tools\Session\Session::get();
		$data['_flash']    = \Katu\Tools\Session\Flash::get();
		$data['_cookies']  = \Katu\Tools\Cookies\Cookie::get();
		$data['_upload']   = [
			'maxSize' => \Katu\Files\Upload::getMaxSize(),
		];

		return $data;
	}

	public static function render(string $template, array $data = [], \Slim\Http\Request $request = null, \Slim\Http\Response $response = null, array $args = [])
	{
		$twig = static::getTwig();
		static::extendTwig($twig);

		$data = array_merge_recursive(static::getCommonData($request, $response, $args), $data);

		return trim($twig->render($template, $data));
	}

	public static function renderCondensed(string $template, array $data = [], \Slim\Http\Request $request = null, \Slim\Http\Response $response = null, array $args = [])
	{
		$template = static::render($template, $data, $request, $response, $args);

		return preg_replace('/[\v\t]/', null, $template);
	}
}
