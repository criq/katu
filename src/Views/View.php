<?php

namespace Katu\Views;

class View {

	static function getTwig($options = []) {
		$dirs = [];

		if (isset($options['dirs']) && $options['dirs']) {
			foreach ($options['dirs'] as $dir) {
				$dirs[] = realpath($dir);
			}
			$dirs = array_filter($dirs);
		}

		if (!isset($dirs) || (isset($dirs) && !$dirs)) {
			$dirs = array_filter([
				realpath(BASE_DIR . '/app/Views/'),
				realpath(\Katu\Files\File::joinPaths(\Katu\Tools\Services\Composer\Composer::getDir(), substr(__DIR__, strcmp(\Katu\Tools\Services\Composer\Composer::getDir(), __DIR__)), 'Views')),
			]);
		}

		$loader = new \Twig\Loader\FilesystemLoader($dirs);
		$twig   = new \Twig\Environment($loader, [
			'cache'       => \Katu\Files\File::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => true,
		]);

		return $twig;
	}

	static function extendTwig(&$twig) {

		/***************************************************************************
		 * Image.
		 */

		$twig->addFunction(new \Twig\TwigFunction('getImage', function($uri) {
			try {
				return new \Katu\Image($uri);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		/***************************************************************************
		 * Text.
		 */

		$twig->addFilter(new \Twig\TwigFilter('shorten', function($string, $length, $options = []) {
			$shorter = substr($string, 0, $length);

			return $shorter;
		}));

		$twig->addFilter(new \Twig\TwigFilter('asArray', function($variable) {
			return (array) $variable;
		}));

		$twig->addFilter(new \Twig\TwigFilter('unique', function($variable) {
			return array_unique($variable);
		}));

		$twig->addFilter(new \Twig\TwigFilter('joinInSentence', function($list, $delimiter, $lastDelimiter) {
			return (new \Katu\Types\TArray($list))->implodeInSentence($delimiter, $lastDelimiter);
		}));

		$twig->addFilter(new \Twig\TwigFilter('isValidDateTime', function($date) {
			try {
				return (new \Katu\Tools\DateTime\DateTime($date))->isValid();
			} catch (\Exception $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig\TwigFilter('markdown', function($text) {
			return \Michelf\Markdown::defaultTransform($text);
		}));

		$twig->addFilter(new \Twig\TwigFilter('nbsp', function($text) {
			return new \Twig_Markup(preg_replace('/\b([aiouksvz])(\s)/i', '\\1&nbsp;', $text), 'UTF-8');
		}));

		/***************************************************************************
		 * Functions.
		 */

		$twig->addFunction(new \Twig\TwigFunction('dump', function() {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig\TwigFunction('getBaseDir', function() {
			return BASE_DIR;
		}));

		// Deprecated.
		$twig->addFunction(new \Twig\TwigFunction('geTURLFor', function() {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('url', function() {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('urlDecoded', function() {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getDecodedFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getCurrenTURL', function() {
			return (string) call_user_func_array(['\Katu\Tools\Routing\URL', 'getCurrent'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('makeUrl', function() {
			return (string) call_user_func_array(['\Katu\Types\TURL', 'make'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getConfig', function() {
			return call_user_func_array(['\Katu\Config', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getCookie', function() {
			return call_user_func_array(['\Katu\Tools\Cookies\Cookie', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getSession', function() {
			return call_user_func_array(['\Katu\Tools\Session\Session', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getFlash', function() {
			return call_user_func_array(['\Katu\Tools\Session\Flash', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig\TwigFunction('getPages', function() {
			$pagination = func_get_arg(0);

			return $pagination->getPaginationPages(func_get_arg(1));
		}));

		$twig->addFunction(new \Twig\TwigFunction('getPaginationUrl', function() {
			$url       =          new \Katu\Types\TURL(func_get_arg(0));
			$page      = (int)    func_get_arg(1);
			$pageIdent = (string) func_get_arg(2);

			$url->removeQueryParam($pageIdent);

			if ($page > 1) {
				$url->addQueryParam($pageIdent, $page);
			}

			return $url->value;
		}));

		$twig->addFunction(new \Twig\TwigFunction('getCsrfToken', function() {
			$params = (array) @func_get_arg(0);

			return \Katu\Tools\Security\CSRF::getFreshToken($params);
		}));

		$twig->addFunction(new \Twig\TwigFunction('getFile', function() {
			return new \Katu\Files\File(BASE_DIR, ltrim(func_get_arg(0), '/'));
		}));

		$twig->addFunction(new \Twig\TwigFunction('getFileUrlWithHash', function() {
			if (func_get_arg(0) instanceof \Katu\Files\File) {
				$file = func_get_arg(0);
			} else {
				$file = new \Katu\Files\File(BASE_DIR, func_get_arg(0));
			}
			$url = new \Katu\Types\TURL($file->geTURL());
			$url->addQueryParam('hash', hash('md4', $file->get()));

			return $url;
		}));

		$twig->addFunction(new \Twig\TwigFunction('lipsum', function($sentences = 10) {
			try {
				return \Katu\Tools\Strings\BaconIpsum::get();
			} catch (\Exception $e) {
				// Nevermind.
			}

			try {
				return implode(' ', (new \Katu\Types\TArray(\Katu\Tools\Strings\Blabot::getList()))->getRandomItems($sentences));
			} catch (\Exception $e) {
				// Nevermind.
			}

			return false;
		}));

		$twig->addFunction(new \Twig\TwigFunction('start', function() {
			if (\Katu\Tools\Profiler\Profiler::isOn()) {
				$profiler = \Katu\Tools\Profiler\Profiler::init('twig');

				return static::render("Katu/Blocks/profilerStart");
			}
		}));

		$twig->addFunction(new \Twig\TwigFunction('stop', function() {
			if (\Katu\Tools\Profiler\Profiler::isOn()) {
				$profiler = \Katu\Tools\Profiler\Profiler::get('twig');

				$res = static::render("Katu/Blocks/profilerEnd", [
					'profiler' => $profiler,
				]);

				$profiler->reset('twig');

				return $res;
			}
		}));

		return true;

	}

	static function getCommonData($request, $response, $args) {
		$app = \Katu\App::get();

		$data['_site']['baseDir'] = BASE_DIR;
		$data['_site']['baseUrl'] = \Katu\Config\Config::get('app', 'baseUrl');
		try {
			$data['_site']['apiUrl']  = \Katu\Config\Config::get('app', 'apiUrl');
		} catch (\Exception $e) {
			/* Doesn't exist. */
		}
		try {
			$data['_site']['timezone'] = \Katu\Config\Config::get('app', 'timezone');
		} catch (\Exception $e) {
			/* Doesn't exist. */
		}

		$data['_request']['uri']    = (string)(string)$request->getUri();
		$data['_request']['url']    = (string)\Katu\Tools\Routing\URL::getCurrent();
		$data['_request']['params'] = (array)$request->getQueryParams();

		if ($request->getAttribute('route')) {
			$data['_request']['route']  = (array)[
				'pattern' => $request->getAttribute('route')->getPattern(),
				'name'    => $request->getAttribute('route')->getName(),
				'params'  => $request->getAttribute('route')->getArguments(),
			];
		}

		$data['_agent'] = new \Jenssegers\Agent\Agent();

		if (class_exists('\App\Models\User')) {
			$data['_user'] = \App\Models\User::getCurrent();
		}

		if (class_exists('\App\Models\Setting')) {
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

	static function render($request, $response, $args, $template, $data, $options = []) {
		$app = \Katu\App::get();

		$twig = static::getTwig($options);
		static::extendTwig($twig);

		$data = array_merge_recursive(static::getCommonData($request, $response, $args), $data);

		return trim($twig->render($template, $data));
	}

	static function renderFromDir($dir, $template, $templateData = []) {
		return self::render($template, $templateData, [
			'dirs' => [
				$dir,
			],
		]);
	}

	static function renderCondensed($template, $templateData = []) {
		$src = self::render($template, $templateData);

		return preg_replace('#[\v\t]#', null, $src);
	}

}
