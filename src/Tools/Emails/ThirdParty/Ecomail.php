<?php

namespace Katu\Tools\Emails\ThirdParty;

use App\Classes\ThirdParty\Google\Cloud\GoogleCloudStorage;
use App\Classes\Time;
use Katu\Errors\Error;
use Katu\Errors\ErrorCollection;
use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Response;
use Katu\Types\TURL;

class Ecomail extends \Katu\Tools\Emails\ThirdParty
{
	public function getEmail(): array
	{
		$email = [];

		if ($this->getTemplate()) {
			$email["message"]["template_id"] = $this->getTemplate();
		}

		$email["message"]["subject"] = $this->getSubject();

		$html = $this->getHtml();

		$dom = \Katu\Tools\DOM\DOM::crawlHTML($html);
		$dom->filter("img")->each(function (\Symfony\Component\DomCrawler\Crawler $e) {
			if (preg_match("/^data:(?<mime>image\/(?<extension>[a-z0-9]+));base64,(?<data>.+)$/m", $e->attr("src"), $match)) {
				$path = implode("/", [
					\Katu\Config\Env::getPlatform(),
					(new Time)->format("Y"),
					(new Time)->format("Y-m-d"),
					implode(".", [
						sha1($match["data"]),
						$match["extension"],
					]),
				]);

				$storage = new GoogleCloudStorage(GoogleCloudStorage::getClient()->bucket("hnutiduha-is-email-public-images"));

				$entity = $storage->writeToPath($path, base64_decode($match["data"]));
				$entity->getStorageObject()->acl()->add("allUsers", "READER");

				$e->getNode(0)->setAttribute("src", "");
				$e->getNode(0)->setAttribute("style", "background-image: url('{$entity->getPublicURL()}'); background-size: cover;");
			}
		});
		$html = trim("<html>{$dom->html()}</html>");

		$email["message"]["html"] = $html;

		$email["message"]["text"] = $this->getPlain() ?: strip_tags($this->html);
		$email["message"]["from_email"] = $this->getSender()->getEmailAddress();
		$email["message"]["from_name"] = $this->getSender()->getName();

		if ($this->getReplyTo()) {
			$email["message"]["reply_to"] = $this->getReplyTo()->getEmailAddress();
		}

		foreach ($this->getRecipients() as $recipient) {
			$email["message"]["to"][] = [
				"email" => $recipient->getEmailAddress(),
				"name" => $recipient->getName(),
			];
		}

		foreach ($this->globalVariables as $name => $content) {
			$email["message"]["global_merge_vars"][] = [
				"name" => $name,
				"content" => $content,
			];
		}

		$email["message"]["attachments"] = array_map(function (Attachment $attachment) {
			return [
				"type" => $attachment->getEntity()->getContentType(),
				"name" => $attachment->getName() ?: $attachment->getEntity()->getFileName(),
				"content" => base64_encode($attachment->getEntity()->getContents()),
			];
		}, $this->getAttachments()->getArrayCopy());

		return $email;
	}

	public function getEndpointURL(): TURL
	{
		return $this->getTemplate()
			? new TURL("http://api2.ecomailapp.cz/transactional/send-template")
			: new TURL("http://api2.ecomailapp.cz/transactional/send-message")
			;
	}

	public function send(): Response
	{
		$curl = new \Curl\Curl;
		$curl->setHeader("key", \Katu\Config\Config::get("ecomail", "api", "key"));
		$curl->setHeader("Content-Type", "application/json");

		$payload = $this->getEmail();
		$res = $curl->post($this->getEndpointURL(), $payload);
		$info = $curl->getInfo();

		if ($info["http_code"] == 200) {
			return (new Response(true))->setPayload($res);
		} else {
			$errors = new ErrorCollection;
			foreach ((array)$res->errors as $key => $error) {
				$errors[] = new Error($error[0], $key);
			}

			// Insert contents of <title>.
			if (!$errors->hasErrors()) {
				try {
					$title = trim(\Katu\Tools\DOM\DOM::crawlHTML($res)->filter("title")->text());
					if ($title) {
						$errors[] = new Error($title);
					}
				} catch (\Throwable $e) {
					// Nevermind.
				}
			}

			// Insert whole response.
			if (!$errors->hasErrors()) {
				$errors[] = new Error((string)$res);
			}

			return (new Response(false))->setPayload($res)->setErrors($errors);
		}
	}
}
