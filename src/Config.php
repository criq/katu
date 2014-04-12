<?php

namespace Katu;

class Config {

	static function get() {
		$filename = BASE_DIR . '/app/Config/' . func_get_arg(0) . '.php';
		if (!is_readable($filename)) {
			throw new Exception("Unable to read config file at " . $filename . ".");
		}

		$config = include $filename;

		$array = new \Katu\Types\FWArray($config);

		return call_user_func_array(array($array, 'getValueByArgs'), array_slice(func_get_args(), 1));
	}

	static function getApp() {
		return call_user_func_array(array('self', 'get'), array_merge(array('app'), func_get_args()));
	}

	static function getDB() {
		return call_user_func_array(array('self', 'get'), array_merge(array('db'), func_get_args()));
	}

}
