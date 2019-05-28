<?php

namespace Katu;

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
				realpath(Utils\FileSystem::joinPaths(Utils\Composer::getDir(), substr(__DIR__, strcmp(Utils\Composer::getDir(), __DIR__)), 'Views')),
			]);
		}

		$loader = new \Twig_Loader_Filesystem($dirs);
		$twig   = new \Twig_Environment($loader, [
			'cache'       => Utils\FileSystem::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => true,
		]);

		return $twig;
	}

	static function extendTwig(&$twig) {

		/***************************************************************************
		 * Image.
		 */

		$twig->addFunction(new \Twig_SimpleFunction('getImage', function($uri) {
			try {
				return new \Katu\Image($uri);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		/***************************************************************************
		 * Text.
		 */

		$twig->addFilter(new \Twig_SimpleFilter('shorten', function($string, $length, $options = []) {
			$shorter = substr($string, 0, $length);

			return $shorter;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('asArray', function($variable) {
			return (array) $variable;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('unique', function($variable) {
			return array_unique($variable);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('joinInSentence', function($list, $delimiter, $lastDelimiter) {
			return (new \Katu\Types\TArray($list))->implodeInSentence($delimiter, $lastDelimiter);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('isValidDateTime', function($date) {
			try {
				return (new Utils\DateTime($date))->isValid();
			} catch (\Exception $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig_SimpleFilter('markdown', function($text) {
			return \Michelf\Markdown::defaultTransform($text);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('nbsp', function($text) {
			return new \Twig_Markup(preg_replace('/\b([aiouksvz])(\s)/i', '\\1&nbsp;', $text), 'UTF-8');
		}));

		/***************************************************************************
		 * Functions.
		 */

		$twig->addFunction(new \Twig_SimpleFunction('dump', function() {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getBaseDir', function() {
			return BASE_DIR;
		}));

		// Deprecated.
		$twig->addFunction(new \Twig_SimpleFunction('getUrlFor', function() {
			return (string) call_user_func_array(['\Katu\Utils\Url', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('url', function() {
			return (string) call_user_func_array(['\Katu\Utils\Url', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('urlDecoded', function() {
			return (string) call_user_func_array(['\Katu\Utils\Url', 'getDecodedFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCurrentUrl', function() {
			return (string) call_user_func_array(['\Katu\Utils\Url', 'getCurrent'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('makeUrl', function() {
			return (string) call_user_func_array(['\Katu\Types\TUrl', 'make'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getConfig', function() {
			return call_user_func_array(['\Katu\Config', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCookie', function() {
			return call_user_func_array(['\Katu\Cookie', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getSession', function() {
			return call_user_func_array(['\Katu\Session', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getFlash', function() {
			return call_user_func_array(['\Katu\Flash', 'get'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPages', function() {
			$pagination = func_get_arg(0);

			return $pagination->getPaginationPages(func_get_arg(1));
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPaginationUrl', function() {
			$url       =          new \Katu\Types\TUrl(func_get_arg(0));
			$page      = (int)    func_get_arg(1);
			$pageIdent = (string) func_get_arg(2);

			$url->removeQueryParam($pageIdent);

			if ($page > 1) {
				$url->addQueryParam($pageIdent, $page);
			}

			return $url->value;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCsrfToken', function() {
			$params = (array) @func_get_arg(0);

			return Utils\CSRF::getFreshToken($params);
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getFile', function() {
			return new \Katu\Utils\File(BASE_DIR, ltrim(func_get_arg(0), '/'));
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getFileUrlWithHash', function() {
			if (func_get_arg(0) instanceof \Katu\Utils\File) {
				$file = func_get_arg(0);
			} else {
				$file = new \Katu\Utils\File(BASE_DIR, func_get_arg(0));
			}
			$url = new \Katu\Types\TUrl($file->getUrl());
			$url->addQueryParam('hash', hash('md4', $file->get()));

			return $url;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('lipsum', function($sentences = 10) {
			try {
				return \Katu\Utils\BaconIpsum::get();
			} catch (\Exception $e) {
				// Nevermind.
			}

			try {
				return implode(' ', (new \Katu\Types\TArray(\Katu\Utils\Blabot::getList()))->getRandomItems($sentences));
			} catch (\Exception $e) {
				// Nevermind.
			}

			return false;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('start', function() {
			if (\Katu\Utils\Profiler::isOn()) {
				$profiler = \Katu\Utils\Profiler::init('twig');

				return static::render("Katu/Blocks/profilerStart");
			}
		}));

		$twig->addFunction(new \Twig_SimpleFunction('stop', function() {
			if (\Katu\Utils\Profiler::isOn()) {
				$profiler = \Katu\Utils\Profiler::get('twig');

				$res = static::render("Katu/Blocks/profilerEnd", [
					'profiler' => $profiler,
				]);

				$profiler->reset('twig');

				return $res;
			}
		}));

		return true;

	}

	static function getCommonData() {
		$app = \Katu\App::get();

		$data['_site']['baseDir'] = BASE_DIR;
		$data['_site']['baseUrl'] = Config::getApp('baseUrl');
		try {
			$data['_site']['apiUrl']  = Config::getApp('apiUrl');
		} catch (\Exception $e) {
			/* Doesn't exist. */
		}
		try {
			$data['_site']['timezone'] = Config::getApp('timezone');
		} catch (\Exception $e) {
			/* Doesn't exist. */
		}

		$data['_request']['uri']    = (string) ($app->request->getResourceUri());
		$data['_request']['url']    = (string) (Utils\Url::getCurrent());
		$data['_request']['params'] = (array)  ($app->request->params());
		$data['_request']['route']  = (array)  ([
			'pattern' => $app->router()->getCurrentRoute()->getPattern(),
			'name'    => $app->router()->getCurrentRoute()->getName(),
			'params'  => $app->router()->getCurrentRoute()->getParams(),
		]);

		$data['_agent'] = new \Jenssegers\Agent\Agent();

		if (class_exists('\App\Models\User')) {
			$data['_user'] = \App\Models\User::getCurrent();
		}

		if (class_exists('\App\Models\Setting')) {
			$data['_settings'] = \App\Models\Setting::getAllAsAssoc();
		}

		$data['_platform'] = Env::getPlatform();
		$data['_config']   = Config::get();
		$data['_session']  = Session::get();
		$data['_cookies']  = Cookie::get();
		$data['_flash']    = Flash::get();
		$data['_upload']   = [
			'maxSize' => Upload::getMaxSize(),
		];

		return $data;
	}

	static function render($template, $templateData = [], $options = []) {
		$app = \Katu\App::get();

		$twig = static::getTwig($options);
		static::extendTwig($twig);

		$data = array_merge(static::getCommonData(), $templateData);

		return trim($twig->render($template . '.twig', $data));
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
