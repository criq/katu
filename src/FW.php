<?php

namespace Jabli;

class FW {

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

		// Timezone.
		try {
			date_default_timezone_set(Config::getApp('timezone'));
		} catch (\Exception $e) {

		}

		// Logger.
		$logger = new \Monolog\Logger(LOGGER_CONTEXT);
		$logger->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$handler = new \Monolog\ErrorHandler($logger);
		$handler->registerErrorHandler(array(), FALSE);
		$handler->registerFatalHandler();

		// Header can and probably will be overwritten by app.
		header('Content-Type: text/html; charset=UTF-8');

		// Session.
		\Jabli\Session::setCookieParams();

		return TRUE;
	}

	static function getApp() {
		$app = \Slim\Slim::getInstance();
		if (!$app) {
			try {
				$config = Config::getApp('slim');
			} catch (Exception $e) {
				$config = array();
			}
			$app = new \Slim\Slim($config);
		}

		return $app;
	}

	static function getDB() {
		return DB\Connection::getInstance();
	}

	static function run() {
		self::initialize();

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

			} catch (\Exception $e) {
				$app->response->setStatus(400);
			}

			$app->response->setStatus(400);
		};

		try {

			$app = self::getApp();

			try {

				// Set up routes.
				foreach ((array) Config::get('routes') as $name => $route) {
					try {
						$_route = $app->map($route->getPattern(), $route->getCallable())->via('GET', 'POST');
						if (is_string($name) && trim($name)) {
							$_route->name($name);
						}
					} catch (\Exception $e) {
						#var_dump($e->getMessage());
					}
				}

			} catch (Exception $e) {
				#var_dump($e->getMessage());
			}

			// Catch-all.
			$app->map('.+', $catch_all)->via('GET', 'POST');

			// Run the app.
			$app->run();

		} catch (\Exception $e) {

			user_error($e);
			die('Error running application.');

		}
	}

}
