<?php

namespace Katu\Tools\Emails;

use Katu\Tools\Services\ServiceInterface;

interface TransactionalEmailServiceInterface extends ServiceInterface
{
	public function dispatch(Email $email): Response;
}
