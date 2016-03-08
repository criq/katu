<?php

namespace Katu\Email;

abstract class ThirdParty extends \Katu\Email {

	public $template;

	public $variables = [];
	public $recipientVariables = [];

	abstract public function send();

	public function setTemplate($template) {
		$this->template = $template;

		return $this;
	}

	public function setVariable($name, $value) {
		$this->variables[$name] = $value;

		return $this;
	}

	public function setRecipientVariable($emailAddress, $name, $value) {
		foreach (static::resolveEmailAddress($emailAddress) as $emailAddress) {
			$this->recipientVariables[$emailAddress][$name] = $value;
		}

		return $this;
	}

}
