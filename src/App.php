<?php

namespace Jabli;

use \Slim\Slim;

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
		$app = Slim::getInstance();
		if (!$app) {
			self::initialize();
			$app = new Slim(Config::get('slim'));
		}

		return $app;
	}

	static function getDB() {
		return DB\Connection::getInstance();
	}

}
