<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Session;
use \Katu\Utils\Url;
use \Katu\Types\TUrl;
use \Facebook\FacebookSession;
use \Facebook\FacebookRedirectLoginHelper;

class Facebook {

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

	static function login($loginUrl, CallbackCollection $callbackCollection = null, $scopes = []) {
		try {

			try {
				$facebook = new \Facebook\Facebook([
					'app_id' => static::getAppId(),
					'app_secret' => static::getSecret(),
					//'default_graph_version' => 'v2.2',
				]);

				var_dump($facebook); die;

				$helper = $fb->getRedirectLoginHelper();

				$permissions = ['email']; // Optional permissions
				$loginUrl = $helper->getLoginUrl('https://example.com/fb-callback.php', $permissions);

				echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
			} catch (\Exception $e) {
				var_dump($e); die;
			}




			$app = App::get();

			$session = static::getSession();

			$helper = new FacebookRedirectLoginHelper((string) $loginUrl);

			// Check the Facebook user.
			$facebookUser = (new \Facebook\FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject(\Facebook\GraphUser::className());

			foreach ($scopes as $scope) {
				if (!isset($sessionScopes)) {
					$sessionScopes = $session->getSessionInfo()->getScopes();
				}
				if (!in_array($scope, $sessionScopes)) {
					return static::redirectToFacebookLoginUrl($helper->getLoginUrl($scopes), true);
				}
			}

			// Login the user.
			if (class_exists('\App\Models\User') && class_exists('\App\Models\UserService')) {
				$userService = \App\Models\UserService::getByServiceAndId('facebook', $facebookUser->getId())->getOne();
				if (!$userService) {

					// Create new user.
					$user = \App\Models\User::create();

					// Assign e-mail address.
					if (class_exists('\App\Models\EmailAddress') && $facebookUser->getProperty('email')) {

						$emailAddress = \App\Models\EmailAddress::make($facebookUser->getProperty('email'));

						if (!\App\Models\User::getBy([
							'emailAddressId' => $emailAddress->id,
						])->getTotal()) {
							$user->setEmailAddress($emailAddress);
							$user->save();
						}

					}

					// Assign user service.
					$userService = $user->addUserService('facebook', $facebookUser->getId());

				}

				$userService->setServiceAccessToken($session->getToken());
				$userService->save();

				$user = $userService->getUser();
				$user->setName($facebookUser->getFirstName(), $facebookUser->getLastName());
				$user->save();

				$user->login();
			}

			if (!isset($user)) {
				$user = null;
			}

			if ($callbackCollection && $callbackCollection->exists('success')) {
				return $callbackCollection->call('success', [$facebookUser, $user]);
			}

			return true;

		// Redirect to login.
		} catch (\Facebook\FacebookAuthorizationException $e) {

			return static::redirectToFacebookLoginUrl($helper->getLoginUrl($scopes), true);

		// Redirect to login.
		} catch (\Facebook\FacebookSDKException $e) {

			return static::redirectToFacebookLoginUrl($helper->getLoginUrl($scopes), true);

		// Invalid token, login.
		} catch (\ErrorException $e) {

			// Redirected back.
			if ($app->request->params('code') && $app->request->params('state')) {

				try {

					$session = $helper->getSessionFromRedirect();
					if ($session) {
						static::setToken($session->getToken());

						return static::redirectToFacebookLoginUrl($loginUrl, false);
					}

				} catch (\Facebook\FacebookSDKException $e) {

					if ($callbackCollection && $callbackCollection->exists('error')) {
						return $callbackCollection->call('error', [$e]);
					}

					throw $e;

				} catch (\Exception $e) {

					if ($callbackCollection && $callbackCollection->exists('error')) {
						return $callbackCollection->call('error', [$e]);
					}

					throw $e;

				}

			// Redirect to login.
			} else {

				return static::redirectToFacebookLoginUrl($helper->getLoginUrl($scopes), true);

			}

		// Other error.
		} catch (\Exception $e) {

			if ($callbackCollection && $callbackCollection->exists('error')) {
				return $callbackCollection->call('error', [$e]);
			}

			throw $e;

		}

		return false;
	}

	static function redirectToFacebookLoginUrl($redirectUrl, $resetToken) {
		if ($resetToken) {
			static::resetToken();
		}

		$state = (new TUrl($redirectUrl))->getQueryParam('state');

		header('Location: ' . $redirectUrl, true, 302);
		die;
	}

	static function redirectToReturnUrl($returnUrl) {
		header('Location: ' . $returnUrl, true, 302);
		die;
	}

	static function startAppSession() {
		Session::start();

		return FacebookSession::setDefaultApplication(Config::get('facebook', 'appId'), Config::get('facebook', 'secret'));
	}

	static function getSession() {
		static::startAppSession();

		return new FacebookSession(static::getToken());
	}

	static function getAppSession() {
		static::startAppSession();

		return new FacebookSession(implode('|', [Config::get('facebook', 'appId'), Config::get('facebook', 'secret')]));
	}

	static function setToken($token) {
		return Session::set('facebook.token', $token);
	}

	static function getToken() {
		return Session::get('facebook.token');
	}

	static function resetToken() {
		return Session::reset('facebook.token');
	}

	static function setUser($userId) {
		return Session::set('facebook.userId', $userId);
	}

	static function getUser($userId) {
		return Session::get('facebook.userId');
	}

	static function getScopes() {
		try {
			return (new \Facebook\FacebookRequest(static::getSession(), 'GET', '/debug_token', [
				'input_token' => static::getToken(),
			]))->execute()->getGraphObject()->getProperty('scopes')->asArray();
		} catch (\Exception $e) {
			return false;
		}
	}

}
