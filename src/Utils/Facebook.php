<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Session;
use \Katu\Utils\Facebook;
use \Katu\Utils\Url;
use \Katu\Types\TURL;
use \Facebook\FacebookSession;
use \Facebook\FacebookRedirectLoginHelper;

class Facebook {

	static function login(TURL $redirectUrl, CallbackCollection $callbackCollection, $scopes = array()) {
		try {

			$app = App::get();

			$session = static::getSession();

			$helper = new FacebookRedirectLoginHelper((string) $redirectUrl);

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

				// Assign e-mail address.
				if (class_exists('\App\Models\EmailAddress') && $facebookUser->getProperty('email')) {

					$emailAddress = \App\Models\EmailAddress::make($facebookUser->getProperty('email'));

					if (!\App\Models\User::getBy(array(
						'emailAddressId' => $emailAddress->id,
					))->getTotal()) {
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
			$user->setName($facebookUser->getName());
			$user->save();

			$user->login();

			return $callbackCollection->call('success', array($facebookUser, $user));

		// Redirect to login.
		} catch (\Facebook\FacebookAuthorizationException $e) {

			return \Katu\Controller::redirect($helper->getLoginUrl($scopes));

		// Invalid token, login.
		} catch (\ErrorException $e) {

			// Redirected back.
			if ($app->request->params('code') && $app->request->params('state')) {

				try {

					$session = $helper->getSessionFromRedirect();
					if ($session) {
						\Katu\Utils\Facebook::setToken($session->getToken());
					} else {
						throw new \Exception();
					}

					return \Katu\Controller::redirect($redirectUrl);

				} catch (\Facebook\FacebookSDKException $e) {

					return $callbackCollection->call('error', array($e));

				} catch (\Exception $e) {

					return $callbackCollection->call('error', array($e));

				}

			// Redirect to login.
			} else {

				return \Katu\Controller::redirect($helper->getLoginUrl($scopes));

			}

		// Other error.
		} catch (\Exception $e) {

			return $callbackCollection->call('error', array($e));

		}

		return self::render("Login/facebook");
	}

	static function startAppSession() {
		Session::start();

		return FacebookSession::setDefaultApplication(Config::get('facebook', 'appId'), Config::get('facebook', 'secret'));
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

	static function setUser($userId) {
		return Session::set('facebook.userId', $userId);
	}

	static function getUser($userId) {
		return Session::get('facebook.userId');
	}

}
