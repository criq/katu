<?php

namespace Katu\Utils;

use \Facebook\FacebookSession;
use \Facebook\FacebookRedirectLoginHelper;

class Facebook {

	const ACCESS_TOKEN_SESSION_KEY = 'facebook.accessToken';

	static function getAppId() {
		try {
			return \Katu\Config::get('facebook', 'api', 'appId');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return \Katu\Config::get('facebook', 'appId');
		}
	}

	static function getSecret() {
		try {
			return \Katu\Config::get('facebook', 'api', 'secret');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return \Katu\Config::get('facebook', 'secret');
		}
	}

	static function getApi() {
		try {
			session_start();
		} catch (\Exception $e) {
			/* Nevermind. */
		}

		return new \Facebook\Facebook([
			'app_id' => static::getAppId(),
			'app_secret' => static::getSecret(),
		]);
	}

	static function getAccessToken() {
		return \Katu\Session::get(static::ACCESS_TOKEN_SESSION_KEY);
	}

	static function setAccessToken($accessToken) {
		return \Katu\Session::set(static::ACCESS_TOKEN_SESSION_KEY, $accessToken);
	}

	static function login($loginUrl, CallbackCollection $callbackCollection = null, $scopes = []) {
		try {

			$app = \Katu\App::get();
			$api = static::getApi();
			$helper = $api->getRedirectLoginHelper();
			$oAuth2Client = $api->getOAuth2Client();

			// Redirected back with code.
			if ($app->request->params('code') && $app->request->params('state')) {

				$accessToken = $helper->getAccessToken();
				if (!$accessToken) {
					throw new \Katu\Exceptions\Facebook\MissingAccessTokenException;
				}

				static::setAccessToken($accessToken);

			// Redirected back with error.
			} elseif ($app->request->params('error') && $app->request->params('state')) {

				if ($callbackCollection && $callbackCollection->exists('error')) {
					return $callbackCollection->call('error', new \Katu\Exceptions\Facebook\ErrorException($helper->getError(), $helper->getErrorCode()));
				}

			}

			// Session.
			$accessToken = static::getAccessToken();

			// Get access token.
			if (!$accessToken) {
				throw new \Katu\Exceptions\Facebook\MissingAccessTokenException;
			}

			// Validate access token.
			$tokenMetadata = $oAuth2Client->debugToken($accessToken);
			if (!$tokenMetadata->getIsValid()) {
				throw new \Katu\Exceptions\Facebook\InvalidAccessTokenException;
			}
			try {
				$tokenMetadata->validateAppId((string) static::getAppId());
				$tokenMetadata->validateExpiration();
			} catch (\Exception $e) {
				throw new \Katu\Exceptions\Facebook\InvalidAccessTokenException;
			}

			// Exchange for long-lived token.
			if (!$accessToken->isLongLived()) {
				try {
					$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
				} catch (\Facebook\Exceptions\FacebookSDKException $e) {
					/* Nevermind. */
				}
			}

			if ($callbackCollection && $callbackCollection->exists('success')) {
				return $callbackCollection->call('success');
			}

			return true;

		// No token, redirect to login.
		} catch (\Katu\Exceptions\Facebook\MissingAccessTokenException $e) {

			return static::redirectToFacebookLoginUrl($helper->getLoginUrl((string) $loginUrl, $scopes));

		// Invalid token, redirect to login.
		} catch (\Katu\Exceptions\Facebook\InvalidAccessTokenException $e) {

			return static::redirectToFacebookLoginUrl($helper->getLoginUrl((string) $loginUrl, $scopes));

		// Redirect to error.
		} catch (\Facebook\Exceptions\FacebookAuthenticationException $e) {

			if ($callbackCollection && $callbackCollection->exists('error')) {
				return $callbackCollection->call('error', $e);
			}

		// Redirect to error.
		} catch (\Facebook\Exceptions\FacebookResponseException $e) {

			if ($callbackCollection && $callbackCollection->exists('error')) {
				return $callbackCollection->call('error', $e);
			}

		// Redirect to login.
		} catch (\Facebook\Exceptions\FacebookSDKException $e) {

			return static::redirectToFacebookLoginUrl($helper->getLoginUrl((string) $loginUrl, $scopes));

		// Other error.
		} catch (\Exception $e) {

			if ($callbackCollection && $callbackCollection->exists('error')) {
				return $callbackCollection->call('error', $e);
			}

			throw $e;

		}

		return false;
	}

	static function redirectToFacebookLoginUrl($redirecTURL) {
		header('Location: ' . (string) $redirecTURL, true, 302);
		die;
	}

	static function getUser() {
		return static::getApi()->get('/me?fields=id,name,email', static::getAccessToken())->getGraphUser();
	}

}
