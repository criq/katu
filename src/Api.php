<?php

namespace Katu;

class Api {

	static function success($res = NULL) {
		$app = App::get();

		$app->response->setStatus(200);

		return Utils\JSON::respond($res);
	}

	static function error($error = NULL, $code = NULL) {
		$app = App::get();

		$app->response->setStatus(400);

		$res = array(
			'error' => array(
				'message' => $error,
			),
		);

		if ($code) {
			$res['error']['code'] = $code;
		}

		return Utils\JSON::respond($res);
	}

	static function getUrl($endpoint) {
		return Utils\Url::joinPaths(Config::get('app', 'apiUrl'), $endpoint);
	}

	static function get($endpoint, $params = array(), &$curl = NULL) {
		return \Amiko\Amiko::get(static::getUrl($endpoint), $params, $curl);
	}

	static function post($endpoint, $params = array(), &$curl = NULL) {
		return \Amiko\Amiko::post(static::getUrl($endpoint), $params, $curl);
	}

}
