<?php

namespace Jabli;

class View {

	static function render($template, $data = array()) {
		$app = \Jabli\FW::getApp();

		$loader = new \Twig_Loader_Filesystem(realpath(BASE_DIR . '/app/Views/'));
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

		$twig->addFunction(new \Twig_SimpleFunction('getCSRFToken', function() {
			return Utils\CSRF::getFreshToken();
		}));

		$data['_server']['base_url'] = Config::get('base_url');
		$data['_server']['api_url']  = Config::get('api_url');
		$data['_server']['timezone'] = Config::get('timezone');

		$data['_user'] = \App\Models\User::getLoggedIn();

		// @todo
		\Jabli\Utils\CSS::implode();

		return trim($twig->render($template . '.tpl', $data));
	}

	static function renderCondensed($template, $data = array()) {
		$src = self::render($template, $data);

		return preg_replace('#[\v\t]#', NULL, $src);
	}

}
