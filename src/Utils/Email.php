<?php

namespace Jabli\Utils;

class Email extends \Swift_Message {

	private $transport;
	private $mailer;

	public function __construct() {
		$config = \Jabli\Config::getSpec('mandrill');

		$this->transport = \Swift_SmtpTransport::newInstance($config['host'], $config['port'])
			->setUsername($config['username'])
			->setPassword($config['password']);

		$this->mailer = \Swift_Mailer::newInstance($this->transport);

		return parent::__construct();
	}

	public function setPlainBody($body) {
		return $this->addPart($body, 'text/plain');
	}

	public function setHTMLBody($body) {
		return $this->addPart($body, 'text/html');
	}

	public function send() {
		return $this->mailer->send($this);
	}

}
