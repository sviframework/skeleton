<?php

namespace Svi\Base\Service;

use Svi\Base\BundleTrait;
use Svi\Base\ContainerAware;
use Svi\Base\Entity\Setting;
use Svi\Base\Manager\SettingManager;

class SettingsService extends ContainerAware
{
    use BundleTrait;

	private $allSettings;

	public function getSettingsKeys()
	{
		return array_keys($this->c->getConfig()->get('settings'));
	}

	public function getSettingName($key)
	{
		$settings = $this->c->getConfig()->get('settings');

		return is_string($settings[$key]) ? $settings[$key] : $settings[$key]['title'];
	}

	public function getSettingType($key)
	{
		$settings = $this->c->getConfig()->get('settings');

		$type = is_array($settings[$key]) && isset($settings[$key]['type']) ? $settings[$key]['type'] : 'textarea';

		return $type;
	}

	public function updateDatabase()
	{
		$exists = $this->getManager()->findBy();

		foreach (array_keys($this->c->getConfig()->get('settings')) as $key) {
			$inDb = null;
			foreach ($exists as $e) {
				if (strtolower($e->getKey()) == strtolower($key)) {
					$inDb = $e;
					break;
				}
			}
			if (!$inDb) {
				$inDb = new Setting();
				$inDb->setKey($key);

				$this->getManager()->save($inDb);
			}
		}
	}

	public function get($key, $default = null)
	{
		$key = strtolower($key);
		$this->fetchAllSettings();

		return isset($this->allSettings[$key]) ? $this->allSettings[strtolower($key)] : $default;
	}

	public function set($key, $value)
	{
		$setting = $this->getSetting($key);
		if ($setting) {
			$setting->setValue($value);
		} else {
			$setting = new Setting();
			$setting->setKey($key);
			$setting->setValue($value);
		}
		$this->getManager()->save($setting);
		$this->allSettings[strtolower($key)] = $value;
	}

	protected function fetchAllSettings()
	{
		if (!isset($this->allSettings)) {
			$this->allSettings = array();
			foreach ($this->createQB()->select('*')->from('setting', '')->execute()->fetchAll() as $v) {
				$this->allSettings[strtolower($v['skey'])] = $v['value'];
			}
		}
	}

	/**
	 * @param $key
	 * @return Setting
	 */
	protected function getSetting($key)
	{
		return $this->getManager()->fetchOne($this->createQB()->where('skey = :key')->setParameter('key', $key));
	}

	/**
	 * @return SettingManager
	 */
	protected function getManager()
	{
		return $this->getSettingsManager();
	}

}
