<?php

namespace Svi\Base;

use Svi\File\FileManager;
use Svi\File\ImageManager;

class Container extends \Svi\Container
{

	/**
	 * @return FormManager
	 */
	public function getFormManager()
	{
		return $this->getApp()->get('manager.sviform');
	}

	/**
	 * @return SettingsManager
	 */
	public function getSettingsManager()
	{
		return $this->getApp()->get('manager.svisettings');
	}

	/**
	 * @return AlertsManager
	 */
	public function getAlertsManager()
	{
		return $this->getApp()->get('manager.svialerts');
	}

	/**
	 * @return FileManager
	 */
	public function getFileManager()
	{
		return $this->getApp()->get('manager.svifile');
	}

	/**
	 * @return ImageManager
	 */
	public function getImageManager()
	{
		return $this->getApp()->get('manager.sviimage');
	}

} 