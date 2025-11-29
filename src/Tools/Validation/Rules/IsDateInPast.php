<?php

namespace Katu\Tools\Validation\Rules;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rule;
use Katu\Tools\Validation\Validation;

class IsDateInPast extends Rule
{
	public function validate(Param $param): Validation
	{
		$validation = new Validation;

		$output = trim($param);
		if (strlen($output)) {
			$output = Time::createFromString(
				$output,
				false,
			);
			if (!strlen($output)) {
				$message = $this->getMessage() ?: "Neplatné datum.";
				$validation->addError((new Error($message, "INVALID_DATE_FORMAT", ErrorVersionCollection::createFromArray([
					"cs" => $message ?: "Neplatné datum.",
					"sk" => $message ?: "Neplatný dátum.",
					"en" => $message ?: "Invalid date format.",
				])))->addParam($param));
			} elseif ($output->isInFuture()) {
				$message = $this->getMessage() ?: "Datum je v budoucnosti.";
				$validation->addError((new Error($message, "DATE_IN_FUTURE", ErrorVersionCollection::createFromArray([
					"cs" => $message ?: "Datum je v budoucnosti.",
					"sk" => $message ?: "Dátum je v budúcnosti.",
					"en" => $message ?: "Date is in the future.",
				])))->addParam($param));
			} else {
				$validation->addParam($param->setOutput($output));
			}
		}

		return $validation;
	}
}
