<?php

namespace Jabli\Aids;

class API {

	static function success($res = NULL) {
		echo JSON::encode($res);

		return TRUE;
	}

	static function error($error = NULL) {
		echo JSON::encode(array(
			'error' => array(
				'message' => $error,
			),
		));

		return TRUE;
	}

	static function getURL($endpoint, $params) {
		return URL::joinPaths(Config::get('api_url'), $endpoint) . '?' . http_build_query($params);
	}

	static function useMethod($method, $endpoint, $params = array()) {
		$curl = new \Curl();
		$curl->$method(URL::joinPaths(Config::get('api_url'), $endpoint), $params);

		if ($curl->http_status_code == 200) {
			return JSON::decodeAsArray($curl->response);
		}

		$array = JSON::decodeAsArray($curl->response);
		if (isset($array['error']['message'])) {
			throw new Exception($array['error']['message']);
		}

		return FALSE;
	}

	static function get($endpoint, $params = array()) {
		return self::useMethod('get', $endpoint, $params);
	}

	static function post($endpoint, $params = array()) {
		return self::useMethod('post', $endpoint, $params);
	}

}
