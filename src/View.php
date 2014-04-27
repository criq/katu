<?php

namespace Katu;

class View {

	static function render($template, $data = array(), $options = array()) {
		$app = \Katu\App::get();

		$dirs = array();

		if (isset($options['dirs']) && $options['dirs']) {
			foreach ($options['dirs'] as $dir) {
				$dirs[] = realpath($dir);
			}
			$dirs = array_filter($dirs);
		}

		if (!isset($dirs) || (isset($dirs) && !$dirs)) {
			$dirs = array_filter(array(
				realpath(BASE_DIR . '/app/Views/'),
				realpath(Utils\FS::joinPaths(Utils\Composer::getDir(), substr(__DIR__, strcmp(Utils\Composer::getDir(), __DIR__)), 'Views')),
			));
		}

		$loader = new \Twig_Loader_Filesystem($dirs);
		$twig   = new \Twig_Environment($loader, array(
			'cache'       => Utils\FS::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => TRUE,
		));

		// Filters.

		$twig->addFilter(new \Twig_SimpleFilter('url', function($string) {
			return Utils\URL::getSite($string);
		}));

		// Functions.

		$twig->addFunction(new \Twig_SimpleFunction('dump', function() {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getURLFor', function() {
			return call_user_func_array(array('\Katu\Utils\URL', 'getFor'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCurrentURL', function() {
			return call_user_func_array(array('\Katu\Utils\URL', 'getCurrent'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getConfig', function() {
			return call_user_func_array(array('\Katu\Config', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCookie', function() {
			return call_user_func_array(array('\Katu\Cookie', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getSession', function() {
			return call_user_func_array(array('\Katu\Session', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getFlash', function() {
			return call_user_func_array(array('\Katu\Flash', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPages', function() {
			$pagination = func_get_arg(0);

			return $pagination->getPaginationPages(func_get_arg(1));
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPaginationURL', function() {
			$url       =          new \Katu\Types\TURL(func_get_arg(0));
			$page      = (int)    func_get_arg(1);
			$pageIdent = (string) func_get_arg(2);

			$url->removeQueryParam($pageIdent);

			if ($page > 1) {
				$url->addQueryParam($pageIdent, $page);
			}

			return $url->value;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCSRFToken', function() {
			return Utils\CSRF::getFreshToken();
		}));

		$data['_site']['baseURL'] = Config::getApp('baseURL');
		$data['_site']['APIURL']  = Config::getApp('apiURL');
		try {
			$data['_site']['timezone'] = Config::getApp('timezone');
		} catch (\Exception $e) {

		}

		if (class_exists('\App\Models\User')) {
			$data['_user'] = \App\Models\User::getLoggedIn();
		}

		$data['_config']  = Config::get();
		$data['_session'] = Session::get();
		$data['_flash']   = Flash::get();

		try {
			if (Config::getApp('css', 'implode')) {
				\Katu\Utils\CSS::implode();
			}
		} catch (\Exception $e) {

		}

		return trim($twig->render($template . '.twig', $data));
	}

	static function renderFromDir($dir, $template, $data = array()) {
		return self::render($template, $data, array(
			'dirs' => array(
				$dir,
			),
		));
	}

	static function renderCondensed($template, $data = array()) {
		$src = self::render($template, $data);

		return preg_replace('#[\v\t]#', NULL, $src);
	}

}
