<?php

namespace Katu;

class App {

	static function init() {
		// Constants.
		if (!defined('BASE_DIR')) {
			define('BASE_DIR', realpath(__DIR__ . '/../../../../'));
		}
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', Utils\FS::joinPaths(BASE_DIR, ErrorHandler::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', Utils\FS::joinPaths(LOG_PATH, ErrorHandler::ERROR_LOG));
		}
		if (!defined('TMP_DIR')) {
			define('TMP_DIR', 'tmp');
		}
		if (!defined('TMP_PATH')) {
			define('TMP_PATH', rtrim(BASE_DIR) . '/' . TMP_DIR . '/');
		}

		// Timezone.
		try {
			date_default_timezone_set(Config::getApp('timezone'));
		} catch (\Exception $e) {
			// Just use default timezone.
		}

		// Session.
		\Katu\Session::setCookieParams();

		// Default content-type header for debugging, will be probably overwritten by app.
		header('Content-Type: text/html; charset=UTF-8');

		return TRUE;
	}

	static function isDev() {
		return Config::get('app', 'slim', 'mode') == 'development';
	}

	static function get() {
		$app = \Slim\Slim::getInstance();
		if (!$app) {

			self::init();

			try {
				$config = Config::getApp('slim');
			} catch (\Exception $e) {
				$config = array();
			}

			// Logger.
			$config['log.writer'] = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
				'handlers' => array(
					new \Monolog\Handler\StreamHandler(ERROR_LOG),
				),
			));

			// Create app.
			$app = new \Slim\Slim($config);

			// Add error middleware.
			$app->add(new Middleware\ErrorMiddleware());

		}

		return $app;
	}

	static function getPDO($name = NULL) {
		$names = array_keys(Config::getDB());

		if ($name) {
			if (!in_array($name, $names)) {
				throw new Exceptions\DatabaseConnectionException("Invalid database connection name.");
			}

			return PDO\Connection::getInstance($name);
		} else {
			if (count($names) > 1) {
				throw new Exceptions\DatabaseConnectionException("Ambiguous database connection name.");
			}
		}

		return PDO\Connection::getInstance($names[0]);
	}

	static function run() {
		self::init();

		$catchAll = function() {
			$app = self::get();

			// Map URL to controller method.
			$parts = array_filter(explode('/', $app->request->getResourceUri()));
			if ($parts) {
				$ns       = '\App\Controllers\\' . implode('\\', array_map('ucfirst', count($parts) > 1 ? array_slice($parts, 0, -1) : $parts));
				$method   = count($parts) > 1 ? array_slice($parts, -1) : 'index';
				$callable = $ns . '::' . (is_array($method) ? $method[0] : $method);

				if (is_callable($callable)) {
					return call_user_func_array($callable, array());
				} else {
					throw new Exceptions\ControllerMethodNotFoundException("Invalid controller method.");
				}
			}
		};

		try {

			$app = self::get();

			try {

				// Set up routes.
				foreach ((array) Config::get('routes') as $name => $route) {
					try {

						throw new \Exception("Error Processing Request", 1);


						$_route = $app->map($route->getPattern(), $route->getCallable())->via('GET', 'POST');
						if (is_string($name) && trim($name)) {
							$_route->name($name);
						} elseif ($route->name) {
							$_route->name($route->name);
						}

					} catch (\Exception $e) {

						throw new Exceptions\ErrorException("A route error occured.");

					}
				}

			} catch (\Exception $e) {

				// Nothing to do, no custom routes defined.

			}

			// Catch-all.
			$app->map('.+', $catchAll)->via('GET', 'POST');

			// Run the app.
			$app->run();

		} catch (Exceptions\NotFoundException $e) {

			Controller::renderNotFound();

			echo $app->response->getBody();

		} catch (Exceptions\UnauthorizedException $e) {

			Controller::renderUnauthorized();

			echo $app->response->getBody();

		} catch (\Exception $e) {

			throw $e;

		}

	}

}
