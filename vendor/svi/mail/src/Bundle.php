<?php

namespace Svi\MailBundle;

use Svi\MailBundle\Service\MailService;

class Bundle extends \Svi\Service\BundlesService\Bundle
{
    use BundleTrait;

	protected function getServices()
	{
		return [
			MailService::class,
		];
	}

} 