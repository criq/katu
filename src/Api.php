<?php

namespace Katu;

class Api
{
	public static function success($res = null, $options = [])
	{
		$app = App::get();
		$app->response->setStatus(200);

		return Utils\JSON::respond($res, $options);
	}

	public static function error($error = null, $code = null)
	{
		$errors = [];

		if ($error instanceof \Katu\Exceptions\ExceptionCollection) {
			foreach ($error as $_error) {
				if ($_error instanceof \Katu\Exceptions\Exception) {
					$errors[] = (string) $_error->getTranslatedMessage();
				} else {
					$errors[] = $_error->getMessage();
				}
			}
			$error = $errors;
		} elseif ($error instanceof \Exception) {
			if ($error instanceof \Katu\Exceptions\Exception) {
				$errors[] = (string) $error->getTranslatedMessage();
			} else {
				$errors[] = $error->getMessage();
			}
		} else {
			$errors[] = $error;
		}

		$res = [
			'error' => [
				'message' => $errors,
			],
		];

		if ($code) {
			$res['error']['code'] = $code;
		}

		return static::errors($res);
	}

	public static function errors($res = [])
	{
		$app = App::get();
		$app->response->setStatus(400);

		return Utils\JSON::respond($res);
	}

	public static function getUrl($endpoint)
	{
		return Utils\Url::joinPaths(Config::get('app', 'apiUrl'), $endpoint);
	}

	public static function get($endpoint, $params = [], &$curl = null)
	{
		return \Amiko\Amiko::get(static::getUrl($endpoint), $params, $curl);
	}

	public static function post($endpoint, $params = [], &$curl = null)
	{
		return \Amiko\Amiko::post(static::getUrl($endpoint), $params, $curl);
	}
}
