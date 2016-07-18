<?php

namespace Svi\Base;

use Svi\Base\Service\AlertsService;
use Svi\Base\Service\FormService;
use Svi\Base\Service\SettingsService;
use Svi\File\Service\FileService;
use Svi\File\Service\ImageService;

class Container extends \Svi\Container
{

	/**
	 * @return FormService
	 */
	public function getFormService()
	{
		return $this->getApp()->get('service.sviform');
	}

	/**
	 * @return SettingsService
	 */
	public function getSettingsService()
	{
		return $this->getApp()->get('service.svisettings');
	}

	/**
	 * @return AlertsService
	 */
	public function getAlertsService()
	{
		return $this->getApp()->get('service.svialerts');
	}

	/**
	 * @return FileService
	 */
	public function getFileService()
	{
		return $this->getApp()->get('service.svifile');
	}

	/**
	 * @return ImageService
	 */
	public function getImageService()
	{
		return $this->getApp()->get('service.sviimage');
	}

} 