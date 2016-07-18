<?php

namespace Svi\Base;

class Bundle extends \Svi\Bundle
{

	protected function getServices()
	{
		return [
			'service.sviform' => 'Service\FormService',
			'service.svisettings' => 'Service\SettingsService',
			'service.svialerts' => 'Service\AlertsService'
		];
	}

} 