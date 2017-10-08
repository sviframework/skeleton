<?php

namespace Svi\Mail;

use Svi\Mail\Service\MailService;

class Bundle extends \Svi\Bundle
{
    use BundleTrait;

	protected function getServices()
	{
		return [
			MailService::class,
		];
	}

} 