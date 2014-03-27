<?php

namespace Jabli;

class View {

	static function render($template, $data = array()) {
		$app = \Jabli\FW::getApp();

		$loader = new \Twig_Loader_Filesystem(array(
			realpath(BASE_DIR . '/app/Views/'),
			realpath(BASE_DIR . '/vendor/jabli/fw/src/Views'),
		));
		$twig   = new \Twig_Environment($loader, array(
			'cache'       => Utils\FS::joinPaths(TMP_PATH, 'twig'),
			'auto_reload' => TRUE,
		));

		$twig->addFilter(new \Twig_SimpleFilter('url', function($string) {
			return Utils\URL::getSite($string);
		}));

		$twig->addFunction(new \Twig_SimpleFunction('dump', function() {
			foreach ((array) func_get_args() as $arg) {
				var_dump($arg);
			}
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getConfig', function() {
			return call_user_func_array(array('\Jabli\Config', 'get'), func_get_args());
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPages', function() {
			$pagination = func_get_arg(0);

			return $pagination->getPaginationPages(func_get_arg(1));
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getPaginationURL', function() {
			$url        =          new \Jabli\Types\URL(func_get_arg(0));
			$page       = (int)    func_get_arg(1);
			$page_ident = (string) func_get_arg(2);

			$url->addParam($page_ident, $page);

			return $url->value;
		}));

		$twig->addFunction(new \Twig_SimpleFunction('getCSRFToken', function() {
			return Utils\CSRF::getFreshToken();
		}));

		$data['_server']['base_url'] = Config::get('base_url');
		$data['_server']['api_url']  = Config::get('api_url');
		$data['_server']['timezone'] = Config::get('timezone');

		$data['_user'] = \App\Models\User::getLoggedIn();

		try {
			if (Config::get('css', 'implode')) {
				\Jabli\Utils\CSS::implode();
			}
		} catch (Exception $e) {}

		return trim($twig->render($template . '.tpl', $data));
	}

	static function renderCondensed($template, $data = array()) {
		$src = self::render($template, $data);

		return preg_replace('#[\v\t]#', NULL, $src);
	}

}
