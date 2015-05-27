<?php

namespace Sv\MailBundle;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'svmail' => 'Mail',
		];
	}

} 