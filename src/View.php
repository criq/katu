<?php

namespace Jabli;

class View {

	static function render($template, $data = array(), $options = array()) {
		$app = \Jabli\FW::getApp();

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

		$twig->addFilter(new \Twig_SimpleFilter('url', function($string) {
			return Types\URL::getSite($string);
		}));

		$twig->addFunction(new \Twig_SimpleFunction('dump', function() {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getConfig', function() {
			return call_user_func_array(array('\Jabli\Config', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCookie', function() {
			return call_user_func_array(array('\Jabli\Cookie', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getSession', function() {
			return call_user_func_array(array('\Jabli\Session', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getFlash', function() {
			return call_user_func_array(array('\Jabli\Flash', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPages', function() {
			$pagination = func_get_arg(0);

			return $pagination->getPaginationPages(func_get_arg(1));
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPaginationURL', function() {
			$url        =          new \Jabli\Types\URL(func_get_arg(0));
			$page       = (int)    func_get_arg(1);
			$page_ident = (string) func_get_arg(2);

			$url->addQueryParam($page_ident, $page);

			return $url->value;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCSRFToken', function() {
			return Utils\CSRF::getFreshToken();
		}));

		$data['_server']['base_url'] = Config::getApp('base_url');
		$data['_server']['api_url']  = Config::getApp('api_url');
		try {
			$data['_server']['timezone'] = Config::getApp('timezone');
		} catch (\Exception $e) {

		}

		if (class_exists('\App\Models\User')) {
			$data['_user'] = \App\Models\User::getLoggedIn();
		}

		try {
			if (Config::getApp('css', 'implode')) {
				\Jabli\Utils\CSS::implode();
			}
		} catch (\Exception $e) {

		}

		return trim($twig->render($template . '.tpl', $data));
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
