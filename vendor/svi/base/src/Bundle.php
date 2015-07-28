<?php

namespace Svi\Base;

class Bundle extends \Svi\Bundle
{

	protected function getServices()
	{
		return [
			'manager.sviform' => 'FormManager',
			'manager.svisettings' => 'SettingsManager',
			'manager.svialerts' => 'AlertsManager'
		];
	}

} 