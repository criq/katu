<?php

namespace Katu;

class Api {

	static function success($res = NULL) {
		$app = App::get();

		$app->response->setStatus(200);

		return Utils\JSON::respond($res);
	}

	static function error($error = NULL) {
		$app = App::get();

		$app->response->setStatus(400);

		return Utils\JSON::respond(array(
			'error' => array(
				'message' => $error,
			),
		));
	}

	static function getURL($endpoint) {
		return Utils\URL::joinPaths(Config::get('app', 'apiUrl'), $endpoint);
	}

	static function get($endpoint, $params = array(), &$curl = NULL) {
		return \Amiko\Amiko::get(static::getURL($endpoint), $params, $curl);
	}

	static function post($endpoint, $params = array(), &$curl = NULL) {
		return \Amiko\Amiko::post(static::getURL($endpoint), $params, $curl);
	}

}
