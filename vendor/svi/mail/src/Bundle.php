<?php

namespace Svi\Mail;

class Bundle extends \Svi\Bundle
{

	protected function getServices()
	{
		return [
			'manager.svimail' => 'MailManager',
		];
	}

} 