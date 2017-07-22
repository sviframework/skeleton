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
			'service.sviform' => 'Service\FormService',
			'service.svisettings' => 'Service\SettingsService',
			'service.svialerts' => 'Service\AlertsService'
		];
	}

	protected function getManagers()
	{
		return [
			'manager.setting' => 'Manager\SettingManager',
		];
	}

	/**
	 * @return FormService
	 */
	public function getFormService()
	{
		return $this->getApp()->get('service.sviform');
	}

	/**
	 * @return AlertsService
	 */
	public function getAlertsService()
	{
		return $this->getApp()->get('service.svialerts');
	}

	/**
	 * @return SettingsService
	 */
	public function getSettingsService()
	{
		return $this->getApp()->get('service.svisettings');
	}

	/**
	 * @return SettingManager
	 */
	public function getSettingsManager()
	{
		return $this->getApp()->get('manager.setting');
	}

} 