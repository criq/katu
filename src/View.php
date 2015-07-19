<?php

namespace Katu;

class View {

	static function render($template, $data = [], $options = []) {
		$app = \Katu\App::get();

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

		// Filters.

		$twig->addFilter(new \Twig_SimpleFilter('thumbnail', function($uri, $size = 640, $quality = 100) {
			try {
				return \Katu\Utils\Image::getThumbnailUrl($uri, $size, $quality);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig_SimpleFilter('squareThumbnail', function($uri, $size = 640, $quality = 100) {
			try {
				return \Katu\Utils\Image::getThumbnailUrl($uri, $size, $quality, ['format' => 'square']);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig_SimpleFilter('thumbnailPath', function($uri, $size = 640, $quality = 100) {
			try {
				return \Katu\Utils\Image::getThumbnailPath($uri, $size, $quality);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig_SimpleFilter('squareThumbnailPath', function($uri, $size = 640, $quality = 100) {
			try {
				return \Katu\Utils\Image::getThumbnailPath($uri, $size, $quality, ['format' => 'square']);
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageWidthAndHeightAttributes', function($path) {
			$size = \Katu\Utils\Image::getSize($path);
			if ($size) {
				return 'width="' . $size->x . '" height="' . $size->y . '"';
			}

			return false;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageWidth', function($path) {
			return \Katu\Utils\Image::getWidth($path);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageHeight', function($path) {
			return \Katu\Utils\Image::getHeight($path);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageColor', function($uri) {
			try {
				return \Katu\Utils\Image::getColorAtCoords(\Katu\Utils\Image::getThumbnailUrl($uri, 1, 100, ['format' => 'square']));
			} catch (\Katu\Exceptions\ImageErrorException $e) {
				return false;
			}
		}));

		$twig->addFilter(new \Twig_SimpleFilter('embedImage', function($path) {
			$mime = @\Katu\Utils\Image::getMime($path);
			$base64 = @base64_encode(@file_get_contents($path));

			if ($mime && $base64) {
				return 'data:' . $mime . ';base64,' . $base64;
			}

			return false;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('shorten', function($string, $length, $options = []) {
			$shorter = substr($string, 0, $length);

			return $shorter;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('asArray', function($variable) {
			return (array) $variable;
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

		// Functions.

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
			return call_user_func_array(['\Katu\Utils\Url', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('url', function() {
			return call_user_func_array(['\Katu\Utils\Url', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCurrentUrl', function() {
			return call_user_func_array(['\Katu\Utils\Url', 'getCurrent'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('makeUrl', function() {
			return call_user_func_array(['\Katu\Types\TUrl', 'make'], func_get_args());
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

		$twig->addFunction(new \Twig_SimpleFunction('blabla', function($sentences = 10) {
			return implode(' ', (new \Katu\Types\TArray(\Katu\Utils\Blabot::getList()))->getRandomItems($sentences));
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

		// Extend Twig.

		if (class_exists('\App\Extensions\View') && method_exists('\App\Extensions\View', 'extendTwig')) {
			\App\Extensions\View::extendTwig($twig);
		}

		$data['_site']['baseDir'] = BASE_DIR;
		$data['_site']['baseUrl'] = Config::getApp('baseUrl');
		$data['_site']['apiUrl']  = Config::getApp('apiUrl');
		try {
			$data['_site']['timezone'] = Config::getApp('timezone');
		} catch (\Exception $e) {

		}

		$data['_request']['uri']    = (string) ($app->request->getResourceUri());
		$data['_request']['url']    = (string) (Utils\Url::getCurrent());
		$data['_request']['params'] = (array)  ($app->request->params());
		$data['_request']['route']  = (array)  ([
			'pattern' => $app->router()->getCurrentRoute()->getPattern(),
			'name'    => $app->router()->getCurrentRoute()->getName(),
		]);

		if (class_exists('\App\Models\User')) {
			$data['_user'] = \App\Models\User::getCurrent();
		}

		$data['_platform'] = Env::getPlatform();
		$data['_config']   = Config::get();
		$data['_session']  = Session::get();
		$data['_flash']    = Flash::get();

		if (class_exists('\App\Models\Setting')) {
			$data['_settings'] = \App\Models\Setting::getAllAsAssoc();
		}

		try {
			if (Config::getApp('css', 'implode')) {
				\Katu\Utils\CSS::implode();
			}
		} catch (\Exception $e) {

		}

		return trim($twig->render($template . '.twig', $data));
	}

	static function renderFromDir($dir, $template, $data = []) {
		return self::render($template, $data, [
			'dirs' => [
				$dir,
			],
		]);
	}

	static function renderCondensed($template, $data = []) {
		$src = self::render($template, $data);

		return preg_replace('#[\v\t]#', null, $src);
	}

}
