<?php

namespace Katu;

use Katu\Exception;

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

		// Timezone.
		try {
			date_default_timezone_set(Config::getApp('timezone'));
		} catch (\Exception $e) {
			// Just use default timezone.
		}

		// Logger.
		$logger = new \Monolog\Logger(LOGGER_CONTEXT);
		$logger->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$handler = new \Monolog\ErrorHandler($logger);
		$handler->registerErrorHandler(array(), FALSE);
		$handler->registerFatalHandler();

		// Session.
		\Katu\Session::setCookieParams();

		return TRUE;
	}

	static function get() {
		$app = \Slim\Slim::getInstance();
		if (!$app) {
			try {
				$config = Config::getApp('slim');
			} catch (\Exception $e) {
				$config = array();
			}
			$app = new \Slim\Slim($config);
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
		}

		return $app;
	}

	static function getDB($name = NULL) {
		$names = array_keys(Config::getDB());

		if ($name) {
			if (!in_array($name, $names)) {
				throw new Exception("Invalid database connection name.");
			}

			return DB\Connection::getInstance($name);
		} else {
			if (count($names) > 1) {
				throw new Exception("Ambiguous database connection name.");
			}
		}

		return DB\Connection::getInstance($names[0]);
	}

	static function run() {
		self::initialize();

		$catch_all = function() {
			$app = self::get();

			// Map URL to controller method.
			$parts = array_filter(explode('/', $app->request->getResourceUri()));
			if ($parts) {
				$ns       = '\App\Controllers\\' . ucfirst($parts[1]);
				$method   = isset($parts[2]) ? $parts[2] : 'index';
				$callable = $ns . '::' . $method;

				if (is_callable($callable)) {
					return call_user_func_array($callable, array());
				} else {
					throw new Exception("Invalid method.");
				}
			}
		};

		try {

			$app = self::get();

			try {

				// Set up routes.
				foreach ((array) Config::get('routes') as $name => $route) {
					try {

						$_route = $app->map($route->getPattern(), $route->getCallable())->via('GET', 'POST');
						if (is_string($name) && trim($name)) {
							$_route->name($name);
						} elseif ($route->name) {
							$_route->name($route->name);
						}

					} catch (\Exception $e) {

						user_error($e);
						die(View::render('FW/Errors/default', array('error' => 'A route error occured.')));

					}
				}

			} catch (\Exception $e) {

				// Nothing to do, no custom routes defined.

			}

			// Catch-all.
			$app->map('.+', $catch_all)->via('GET', 'POST');

			// Run the app.
			$app->run();

		} catch (\Exception $e) {

			user_error($e);
			die(View::render('FW/Errors/default', array('error' => 'Error running application.')));

		}
	}

}
