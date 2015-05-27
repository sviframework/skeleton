<?php

namespace Sv\BaseBundle;

use Sv\FileBundle\FileManager;
use Sv\FileBundle\ImageManager;

class Container extends \Svi\Container
{

	/**
	 * @return FormManager
	 */
	public function getFormManager()
	{
		return $this->getApp()->get('manager.form');
	}

	/**
	 * @return SettingsManager
	 */
	public function getSettingsManager()
	{
		return $this->getApp()->get('manager.settings');
	}

	/**
	 * @return AlertsManager
	 */
	public function getAlertsManager()
	{
		return $this->getApp()->get('manager.alerts');
	}

	/**
	 * @return FileManager
	 */
	public function getFileManager()
	{
		return $this->getApp()->get('manager.file');
	}

	/**
	 * @return ImageManager
	 */
	public function getImageManager()
	{
		return $this->getApp()->get('manager.image');
	}

} 