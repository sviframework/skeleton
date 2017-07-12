<?php

namespace Svi\Mail;

use Svi\Mail\Service\MailService;

class Bundle extends \Svi\Bundle
{

	protected function getServices()
	{
		return [
			'service.svimail' => 'Service\MailService',
		];
	}

	/**
	 * @return MailService
	 */
	public function getMailService()
	{
		return $this->getApp()->get('service.svimail');
	}

} 