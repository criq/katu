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
				realpath(Utils\FS::joinPaths(Utils\Composer::getDir(), substr(__DIR__, strcmp(Utils\Composer::getDir(), __DIR__)), 'Views')),
			]);
		}

		$loader = new \Twig_Loader_Filesystem($dirs);
		$twig   = new \Twig_Environment($loader, [
			'cache'       => Utils\FS::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => TRUE,
		]);

		// Filters.

		$twig->addFilter(new \Twig_SimpleFilter('url', function($string) {
			return Utils\Url::getSite($string);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('thumbnail', function($uri, $size = 640, $quality = 100) {
			return \Katu\Utils\Image::getThumbnailUrl($uri, $size, $quality);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('squareThumbnail', function($uri, $size = 640, $quality = 100) {
			return \Katu\Utils\Image::getThumbnailUrl($uri, $size, $quality, ['format' => 'square']);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('thumbnailPath', function($uri, $size = 640, $quality = 100) {
			return \Katu\Utils\Image::getThumbnailPath($uri, $size, $quality);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('squareThumbnailPath', function($uri, $size = 640, $quality = 100) {
			return \Katu\Utils\Image::getThumbnailPath($uri, $size, $quality, ['format' => 'square']);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageWidthAndHeightAttributes', function($path) {
			$size = \Katu\Utils\Image::getSize($path);
			if ($size) {
				return 'width="' . $size->x . '" height="' . $size->y . '"';
			}

			return FALSE;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageWidth', function($path) {
			return \Katu\Utils\Image::getWidth($path);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('imageHeight', function($path) {
			return \Katu\Utils\Image::getHeight($path);
		}));

		$twig->addFilter(new \Twig_SimpleFilter('embedImage', function($path) {
			$mime = @\Katu\Utils\Image::getMime($path);
			$base64 = @base64_encode(@file_get_contents($path));

			if ($mime && $base64) {
				return 'data:' . $mime . ';base64,' . $base64;
			}

			return FALSE;
		}));

		$twig->addFilter(new \Twig_SimpleFilter('shorten', function($string, $length, $options = []) {
			$shorter = substr($string, 0, $length);

			return $shorter;
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

		$twig->addFunction(new \Twig_SimpleFunction('getUrlFor', function() {
			return call_user_func_array(['\Katu\Utils\Url', 'getFor'], func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCurrentUrl', function() {
			return call_user_func_array(['\Katu\Utils\Url', 'getCurrent'], func_get_args());
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

		$data['_request']['uri']    = $app->request->getResourceUri();
		$data['_request']['params'] = $app->request->params();
		if ($app->request->params('returnUri')) {
			$data['_request']['returnUrl'] = (string) \Katu\Utils\Url::getSite($app->request->params('returnUri'));
		}

		if (class_exists('\App\Models\User')) {
			$data['_user'] = \App\Models\User::getCurrent();
		}

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

		return preg_replace('#[\v\t]#', NULL, $src);
	}

}
