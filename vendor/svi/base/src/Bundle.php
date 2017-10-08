<?php

namespace Svi\Base;

use Svi\Base\Manager\SettingManager;
use Svi\Base\Service\AlertsService;
use Svi\Base\Service\FormService;
use Svi\Base\Service\SettingsService;

class Bundle extends \Svi\Bundle
{
    use BundleTrait;

	protected function getServices()
	{
		return [
			FormService::class,
			SettingsService::class,
			AlertsService::class,
		];
	}

	protected function getManagers()
	{
		return [
			SettingManager::class,
		];
	}

} 