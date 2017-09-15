<?php

namespace Svi\Base;

use Svi\Base\Manager\SettingManager;
use Svi\Base\Service\AlertsService;
use Svi\Base\Service\FormService;
use Svi\Base\Service\SettingsService;

class Bundle extends \Svi\Bundle
{

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

	/**
	 * @return FormService
	 */
	public function getFormService()
	{
		return $this->getApp()->get(FormService::class);
	}

	/**
	 * @return AlertsService
	 */
	public function getAlertsService()
	{
		return $this->getApp()->get(AlertsService::class);
	}

	/**
	 * @return SettingsService
	 */
	public function getSettingsService()
	{
		return $this->getApp()->get(SettingsService::class);
	}

	/**
	 * @return SettingManager
	 */
	public function getSettingsManager()
	{
		return $this->getApp()->get(SettingManager::class);
	}

} 