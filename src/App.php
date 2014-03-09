<?php

namespace Jabli;

class App {

	static function initialize() {
		// Constants.
		if (!defined('BASE_DIR')) {
			define('BASE_DIR', realpath(__DIR__ . '/../../../../'));
		}
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', rtrim(BASE_DIR) . '/logs/');
		}
		if (!defined('TMP_PATH')) {
			define('TMP_PATH', rtrim(BASE_DIR) . '/tmp/');
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', LOG_PATH . 'error.log');
		}
		if (!defined('LOGGER_CONTEXT')) {
			define('LOGGER_CONTEXT', 'app');
		}

		try {
			date_default_timezone_set(Config::get('timezone'));
		} catch (Exception $e) {

		}

		// Logger.
		$logger = new \Monolog\Logger(LOGGER_CONTEXT);
		$logger->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$handler = new \Monolog\ErrorHandler($logger);
		$handler->registerErrorHandler(array(), FALSE);
		$handler->registerFatalHandler();
	}

	static function getApp() {
		$app = \Slim\Slim::getInstance();
		if (!$app) {
			self::initialize();
			$config = array(
				'templates.path' => './app/Views/',
			);
			$config = array_merge($config, Config::get('slim'));
			$app = new \Slim\Slim($config);
		}

		return $app;
	}

	static function getDB() {
		return DB\Connection::getInstance();
	}

	static function run() {
		$catch_all = function() {
			$app = self::getApp();

			try {

				// Map URL to controller method.
				$parts = array_filter(explode('/', $app->request->getResourceUri()));
				if ($parts) {
					$ns       = '\App\Controllers\\' . implode('\\', array_map('ucfirst', array_slice($parts, 0, -1)));
					$method   = array_slice($parts, -1);
					$callable = $ns . '::' . $method[0];

					if (is_callable($callable)) {
						return call_user_func_array($callable, array());
					} else {
						throw new Exception("Invalid method.");
					}
				}

			} catch (Exception $e) {
				$app->response->setStatus(400);
			}

			$app->response->setStatus(400);
		};

		$app = self::getApp();

		// Set up routes.
		$routes = Config::getSpec('routes');
		foreach ($routes as $url => $callable) {
			$app->get( $url, array("\App\Controllers\\" . $callable[0], $callable[1]));
			$app->post($url, array("\App\Controllers\\" . $callable[0], $callable[1]));
		}

		// Catch-all.
		$app->get( '.+', $catch_all);
		$app->post('.+', $catch_all);

		$app->run();
	}

}
