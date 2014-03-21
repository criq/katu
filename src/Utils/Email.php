<?php

namespace Jabli\Utils;

class Email {

	static function send($params) {
		$mailer = \Swift_Mailer::newInstance(\Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs'));
		$message = \Swift_Message::newInstance();

		$message->setSubject($params['subject']);
		$message->setTo($params['to']);

		// Plain.
		if (strip_tags($params['body']) == $params['body']) {
			$message->setBody($params['body'], 'text/plain');

		// HTML.
		} else {
			$message->setBody($params['body'], 'text/html');
			$message->addPart(strip_tags($params['body']), 'text/plain');
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
