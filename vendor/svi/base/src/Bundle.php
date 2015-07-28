<?php

namespace Svi\Base;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'sviform' => 'Form',
			'svisettings' => 'Settings',
			'svialerts' => 'Alerts'
		];
	}

} 