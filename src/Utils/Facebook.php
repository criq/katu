<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Session;
use \Katu\Utils\Facebook;
use \Katu\Utils\URL;
use \Facebook\FacebookSession;
use \Facebook\FacebookRedirectLoginHelper;

class Facebook {

	static function login($callbacks) {
		try {

			$app = App::get();

			Session::start();

			FacebookSession::setDefaultApplication(Config::get('facebook', 'appID'), Config::get('facebook', 'secret'));
			$helper = new FacebookRedirectLoginHelper((string) URL::getFor('login.facebook'));

			$session = Facebook::getSession();
			#var_dump($session->getSessionInfo()); die;

			$request = new \Facebook\FacebookRequest($session, 'GET', '/me');

			$facebookUser = $request->execute()->getGraphObject(\Facebook\GraphUser::className());

			// Login the user.
			$userService = \App\Models\UserService::getByServiceAndID('facebook', $facebookUser->getId())->getOne();
			if (!$userService) {

				// Create new user.
				$user = \App\Models\User::create();
				$userService = $user->addUserService('facebook', $facebookUser->getId());

			}

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

					return \Katu\Controller::redirect(URL::getFor('login.facebook'));

				} catch (\Facebook\FacebookSDKException $e) {

					// Show error?
					return \Katu\Controller::redirect($helper->getLoginUrl());

				} catch (\Exception $e) {

					// Show error?
					return \Katu\Controller::redirect($helper->getLoginUrl());

				}

			// Redirect to login.
			} else {

				return \Katu\Controller::redirect($helper->getLoginUrl());

			}

		// Other error.
		} catch (\Exception $e) {

			// Show error?
			return \Katu\Controller::redirect($helper->getLoginUrl());

		}

		return self::render("Login/facebook");
	}

	static function getSession() {
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
