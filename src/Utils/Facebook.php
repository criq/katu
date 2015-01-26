<?php

namespace Katu\Utils;

use \Katu\App;
use \Katu\Config;
use \Katu\Session;
use \Katu\Utils\Facebook;
use \Katu\Utils\Url;
use \Katu\Types\TUrl;
use \Facebook\FacebookSession;
use \Facebook\FacebookRedirectLoginHelper;

class Facebook {

	const REDIRECT_URLS_KEY = 'facebook.redirectUrls';
	const SCENARIO_RETURN_URLS_KEY = 'facebook.scenarioReturnUrls';

	static function login(TUrl $oAuthRedirectUrl, TUrl $scenarioReturnUrl, CallbackCollection $callbackCollection = null, $scopes = []) {
		try {

			$app = App::get();

			$session = static::getSession();

			$helper = new FacebookRedirectLoginHelper((string) $oAuthRedirectUrl);

			// Check the Facebook user.
			$facebookUser = (new \Facebook\FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject(\Facebook\GraphUser::className());

			foreach ($scopes as $scope) {
				if (!isset($sessionScopes)) {
					$sessionScopes = $session->getSessionInfo()->getScopes();
				}
				if (!in_array($scope, $sessionScopes)) {
					return static::redirectToLoginUrl($helper->getLoginUrl($scopes), $scenarioReturnUrl);
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

			if ($callbackCollection && $callbackCollection->exists('success')) {
				return $callbackCollection->call('success', [static::getScenarioReturnUrl($app->request->params('state')), $facebookUser, $user]);
			}

			return true;

		// Redirect to login.
		} catch (\Facebook\FacebookAuthorizationException $e) {

			static::resetToken();

			return static::redirectToLoginUrl($helper->getLoginUrl($scopes), $scenarioReturnUrl);

		// Redirect to login.
		} catch (\Facebook\FacebookSDKException $e) {

			static::resetToken();

			return static::redirectToLoginUrl($helper->getLoginUrl($scopes), $scenarioReturnUrl);

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

					$controllerClass = \Katu\App::getControllerClass();

					return $controllerClass::redirect(static::getRedirectUrl($app->request->params('state')));

				} catch (\Facebook\FacebookSDKException $e) {

					if ($callbackCollection && $callbackCollection->exists('error')) {
						return $callbackCollection->call('error', [static::getScenarioReturnUrl($app->request->params('state')), $e]);
					}

					throw $e;

				} catch (\Exception $e) {

					if ($callbackCollection && $callbackCollection->exists('error')) {
						return $callbackCollection->call('error', [static::getScenarioReturnUrl($app->request->params('state')), $e]);
					}

					throw $e;

				}

			// Redirect to login.
			} else {

				static::resetToken();

				return static::redirectToLoginUrl($helper->getLoginUrl($scopes), $scenarioReturnUrl);

			}

		// Other error.
		} catch (\Exception $e) {

			if ($callbackCollection && $callbackCollection->exists('error')) {
				return $callbackCollection->call('error', [static::getScenarioReturnUrl($app->request->params('state')), $e]);
			}

			throw $e;

		}

		return self::render("Login/facebook");
	}

	static function redirectToLoginUrl($redirectUrl, $scenarioReturnUrl) {
		$state = (new TUrl($redirectUrl))->getQueryParam('state');

		Session::add(static::REDIRECT_URLS_KEY, (string) $redirectUrl, $state);
		Session::add(static::SCENARIO_RETURN_URLS_KEY, (string) $scenarioReturnUrl, $state);

		header('Location: ' . $redirectUrl, true, 302); die;
	}

	static function getRedirectUrl($state) {
		$redirectUrls = Session::get(static::REDIRECT_URLS_KEY);
		if (isset($redirectUrls[$state])) {
			return $redirectUrls[$state];
		}

		return null;
	}

	static function getScenarioReturnUrl($state) {
		$scenarioReturnUrls = Session::get(static::SCENARIO_RETURN_URLS_KEY);
		if (isset($scenarioReturnUrls[$state])) {
			return $scenarioReturnUrls[$state];
		}

		return null;
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

}
