<?php

namespace Jabli\Utils;

class Email {

	static function send($params) {
		$mailer = \Swift_Mailer::newInstance(\Swift_SendmailTransport::newInstance('/usr/sbin/sendmail'));
		$message = \Swift_Message::newInstance();

		$message->setSubject($params['subject']);
		$message->setTo($params['to']);

		if (isset($params['body'])) {
			if (strip_tags($params['body']) == $params['body']) {
				// Plain.
				$message->setBody($params['body'], 'text/plain');
			} else {
				// HTML.
				$message->setBody($params['body'], 'text/html');
				$message->addPart(strip_tags($params['body']), 'text/plain');
			}
		}

		if (isset($params['body_plain'])) {
			$message->addPart($params['body_plain'], 'text/plain');
		}

		if (isset($params['body_html'])) {
			$message->addPart($params['body_html'], 'text/html');
		}

		if (isset($params['from'])) {
			$message->setFrom($params['from']);
		}

		if (isset($params['bcc'])) {
			$message->setBcc($params['bcc']);
		}

		return $mailer->send($message);
	}

}
