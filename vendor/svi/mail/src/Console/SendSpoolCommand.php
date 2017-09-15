<?php

namespace Svi\Mail\Console;

use Svi\ConsoleCommand;
use Svi\Mail\Service\MailService;

class SendSpoolCommand extends ConsoleCommand
{
	public function getName()
	{
		return 'mail:send-spool';
	}

	public function getDescription()
	{
		return 'Sends mail messages from spool';
	}

	public function execute(array $args)
	{
		$this->getApp()->get(MailService::class)->sendSpool();
	}

} 