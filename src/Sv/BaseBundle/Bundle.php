<?php

namespace Sv\BaseBundle;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'form' => 'Form',
			'settings' => 'Settings',
			'alerts' => 'Alerts'
		];
	}

} 