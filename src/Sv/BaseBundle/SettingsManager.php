<?php

namespace Sv\BaseBundle;

use Sv\BaseBundle\ContainerAware;
use Sv\BaseBundle\Entity\Setting;

class SettingsManager extends ContainerAware
{
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

		$type = @$settings[$key]['type'];
		if (!$type) {
			$type = 'textarea';
		}

		return $type;
	}

	public function updateDatabase()
	{
		$exists = Setting::findBy();

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
				$inDb->save();
			}
		}
	}

	public function get($key)
	{
		$this->fetchAllSettings();

		return @$this->allSettings[strtolower($key)];
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
		$setting->save();
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
		return Setting::fetchOne($this->createQB()->where('skey = :key')->setParameter('key', $key));
	}

}
