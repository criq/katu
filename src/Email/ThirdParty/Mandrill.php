<?php

namespace Katu\Email\ThirdParty;

class Mandrill extends \Katu\Email\ThirdParty
{

	public $async = false;
	public $attachments = [];
	public $content = [];

	public static function getDefaultApi()
	{
		$app = \Katu\App::get();

		try {
			$key = \Katu\Config::get('app', 'email', 'useMandrillKey');
		} catch (\Exception $e) {
			$key = 'live';
		}

		return new \Mandrill(\Katu\Config::get('mandrill', 'api', 'keys', $key));
	}

	public function setContent($name, $value)
	{
		$this->content[$name] = $value;

		return $this;
	}

	public function setAsync($async = true)
	{
		$this->async = (bool) $async;

		return $this;
	}

	public function getMessage($message)
	{
		$message['subject']    = $this->subject;
		$message['html']       = $this->html;
		$message['text']       = $this->plain;
		$message['from_email'] = $this->fromEmailAddress;
		$message['from_name']  = $this->fromName;
		$message['headers']    = $this->headers;

		foreach ($this->to as $toEmailAddress => $toName) {
			$message['to'][] = [
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'to',
			];
		}

		foreach ($this->cc as $toEmailAddress => $toName) {
			$message['to'][] = [
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'cc',
			];
		}

		foreach ($this->attachments as $attachment) {
			$message['attachments'][] = [
				'name'    => (string) $attachment['file'],
				'type'    => $attachment['file']->getMime(),
				'content' => base64_encode($attachment['file']->get()),
			];
		}

		$message['global_merge_vars'] = $this->getVariables();
		$message['merge_vars'] = $this->getRecipientVariables();

		$message['async'] = $this->async;

		return $message;
	}

	public function getContent()
	{
		$content = [];

		foreach ($this->content as $name => $value) {
			$content[] = [
				'name'    => $name,
				'content' => $value,
			];
		}

		return $content;
	}

	public function getVariables()
	{
		$variables = [];

		foreach ($this->variables as $name => $value) {
			$variables[] = [
				'name'    => $name,
				'content' => $value,
			];
		}

		return $variables;
	}

	public function getRecipientVariables()
	{
		$variables = [];

		foreach ($this->recipientVariables as $recipient => $vars) {
			$recipientVars = [];
			foreach ($vars as $name => $value) {
				$recipientVars[] = [
					'name'    => $name,
					'content' => $value,
				];
			}
			$variables[] = [
				'rcpt' => $recipient,
				'vars' => $recipientVars,
			];
		}

		return $variables;
	}

	public function send()
	{
		$args = [];
		if (isset($args[0]) && $args[0] instanceof \Mandrill) {
			$mandrillApi = $args[0];
		} else {
			$mandrillApi = static::getDefaultApi();
		}

		if (isset($args[1]) && is_array($args[1])) {
			$message = $args[1];
		} else {
			$message = [];
		}

		if ($this->template) {
			return $mandrillApi->messages->sendTemplate($this->template, $this->getContent(), $this->getMessage($message));
		} else {
			return $mandrillApi->messages->send($this->getMessage($message));
		}
	}
}
