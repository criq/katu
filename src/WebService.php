<?php

namespace Elementary;

class WebService {

	static function success($res = NULL) {
		echo JSON::encode($res);

		return TRUE;
	}

	static function error($error) {
		echo JSON::encode(array(
			'error' => array(
				'message' => $error,
			),
		));

		return TRUE;
	}

	static function post($endpoint, $params) {
		$curl = new \Curl();
		$curl->post(URL::joinPaths(BASE_URL, $endpoint), $params);

		if ($curl->http_status_code == 200) {
			return TRUE;
		}

		$array = JSON::decodeAsArray($curl->response);
		if (isset($array['error']['message'])) {
			throw new Exception($array['error']['message']);
		}

		return FALSE;
	}

}
