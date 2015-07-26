<?php

namespace Svi\Base;

class Bundle extends \Svi\Bundle
{

	protected function getManagers()
	{
		return [
			'svform' => 'Form',
			'svsettings' => 'Settings',
			'svalerts' => 'Alerts'
		];
	}

} 