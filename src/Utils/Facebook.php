<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Session;
use \Katu\Utils\Facebook;
use \Katu\Utils\URL;
use \Katu\Types\TURL;
use \Facebook\FacebookSession;
use \Facebook\FacebookRedirectLoginHelper;

class Facebook {

	static function login(TURL $redirectURL, Callbacks $callbacks, $scopes = array()) {
		try {

			$app = App::get();

			$session = static::getSession();

			$helper = new FacebookRedirectLoginHelper((string) $redirectURL);

			// Check the Facebook user.
			$facebookUser = (new \Facebook\FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject(\Facebook\GraphUser::className());

			// Check scopes.
			$sessionInfo = $session->getSessionInfo();
			foreach ($scopes as $scope) {
				if (!in_array($scope, $sessionInfo->getScopes())) {
					throw new \Facebook\FacebookSDKException("Missing " . $scope . " scope.");
				}
			}

			// Login the user.
			$userService = \App\Models\UserService::getByServiceAndID('facebook', $facebookUser->getId())->getOne();
			if (!$userService) {

				// Create new user.
				$user = \App\Models\User::create();
				$userService = $user->addUserService('facebook', $facebookUser->getId());

			}

			$userService->setServiceAccessToken($session->getToken());
			$userService->save();

			$user = $userService->getUser();
			$user->setName($facebookUser->getName());
			$user->save();

			$user->login();

			return $callbacks->call('success');

		// Invalid token, login.
		} catch (\Facebook\FacebookSDKException $e) {

			// Redirected back.
			if ($app->request->params('code') && $app->request->params('state')) {

				try {

					$session = $helper->getSessionFromRedirect();
					if ($session) {
						\Katu\Utils\Facebook::setToken($session->getToken());
					} else {
						throw new \Exception();
					}

					return \Katu\Controller::redirect($redirectURL);

				} catch (\Facebook\FacebookSDKException $e) {

					return $callbacks->call('error');

				} catch (\Exception $e) {

					return $callbacks->call('error');

				}

			// Redirect to login.
			} else {

				return \Katu\Controller::redirect($helper->getLoginUrl($scopes));

			}

		// Other error.
		} catch (\Exception $e) {

			return $callbacks->call('error');

		}

		return self::render("Login/facebook");
	}

	static function startAppSession() {
		Session::start();

		return FacebookSession::setDefaultApplication(Config::get('facebook', 'appID'), Config::get('facebook', 'secret'));
	}

	static function getSession() {
		static::startAppSession();

		return new \Facebook\FacebookSession(static::getToken());
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

	static function setUser($userID) {
		return Session::set('facebook.userID', $userID);
	}

	static function getUser($userID) {
		return Session::get('facebook.userID');
	}

}
