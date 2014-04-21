<?php

namespace Katu;

class API {

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

	static function getURL($endpoint, $params) {
		return Utils\URL::joinPaths(Config::getApp('apiURL'), $endpoint) . '?' . http_build_query($params);
	}

	static function useMethod($method, $endpoint, $params = array(), &$curl = NULL) {
		$curl = new \Curl();
		$curl->$method(Utils\URL::joinPaths(Config::getApp('apiURL'), $endpoint), $params);

		$response = $curl->response;
		if (is_array($response)) {
			$response = Utils\JSON::encode($response);
		}

		// Success.
		if ($curl->http_status_code == 200) {
			return Utils\JSON::decodeAsArray($response);
		}

		// Error.
		$array = Utils\JSON::decodeAsArray($response);
		if (isset($array['error']['message'])) {
			throw new Exception($array['error']['message']);
		} else {
			throw new Exception($curl->error_message, $curl->error_code);
		}

		return FALSE;
	}

	static function get($endpoint, $params = array(), &$curl = NULL) {
		return self::useMethod('get', $endpoint, $params, $curl);
	}

	static function post($endpoint, $params = array(), &$curl = NULL) {
		return self::useMethod('post', $endpoint, $params, $curl);
	}

}
