<?php

namespace Svi\BaseBundle;

use Svi\BaseBundle\Manager\SettingManager;
use Svi\BaseBundle\Service\AlertsService;
use Svi\BaseBundle\Service\FormService;
use Svi\BaseBundle\Service\SettingsService;

class Bundle extends \Svi\Service\BundlesService\Bundle
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