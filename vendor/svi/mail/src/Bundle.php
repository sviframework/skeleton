<?php

namespace Svi\Mail;

use Svi\Mail\Service\MailService;

class Bundle extends \Svi\Bundle
{

	protected function getServices()
	{
		return [
			MailService::class,
		];
	}

	/**
	 * @return MailService
	 */
	public function getMailService()
	{
		return $this->getApp()->get(MailService::class);
	}

} 