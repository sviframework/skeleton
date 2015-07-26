<?php

namespace Svi\Mail;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'svimail' => 'Mail',
		];
	}

} 