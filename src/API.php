<?php

namespace Jabli;

class API {

	static function success($res = NULL) {
		echo Utils\JSON::encode($res);

		return TRUE;
	}

	static function error($error = NULL) {
		echo Utils\JSON::encode(array(
			'error' => array(
				'message' => $error,
			),
		));

		return TRUE;
	}

	static function getURL($endpoint, $params) {
		return Utils\URL::joinPaths(Config::get('api_url'), $endpoint) . '?' . http_build_query($params);
	}

	static function useMethod($method, $endpoint, $params = array(), &$curl = NULL) {
		$curl = new \Curl();
		$curl->$method(Utils\URL::joinPaths(Config::get('api_url'), $endpoint), $params);

		if ($curl->http_status_code == 200) {
			return Utils\JSON::decodeAsArray($curl->response);
		}

		$array = Utils\JSON::decodeAsArray($curl->response);
		if (isset($array['error']['message'])) {
			throw new Exception($array['error']['message']);
		} else {
			throw new Exception("An error occured.");
			trigger_error($curl->response);
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
