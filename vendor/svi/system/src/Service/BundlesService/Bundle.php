<?php

namespace Svi\Service\BundlesService;

use Svi\Application;

abstract class Bundle
{
	private $name;
	private $namespace;
	private $app;
	private $services = [];
	private $managers = [];

	function __construct(Application $app)
	{
		$this->app = $app;
		$this->loadConfig();
		$this->loadServices();
		$this->loadManagers();
	}

	/**
	 * @return Application
	 */
	public function getApp()
	{
		return $this->app;
	}

	public function getTranslations($lang)
	{
		$file = $this->getDir() . '/Translations/' . strtolower($lang) . '.php';
		if (file_exists($file)) {
			return include $file;
		}

		return [];
	}

	/**
	 * @return array
	 */
	public function getEntityClasses()
	{
		$result = [];

		foreach ($this->getManagerInstances() as $manager) {
			$result = $manager->getEntityClassName();
		}

		return $result;
	}

	/**
	 * @return Manager[]
	 */
	public function getManagerInstances()
	{
		$result = [];

		foreach ($this->managers as $managerName) {
			$result[] = $this->app[$managerName];
		}

		return $result;
	}

	public function getCommandClasses()
	{
		$result = array();
		$dir = $this->getDir() . '/Console';
		if (file_exists($dir)) {
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if (preg_match('/^((?:.*)Command)\.php$/', $entry, $matches)) {
					$result[] = $this->getNamespace() . '\\Console\\' . $matches[1];
				}
			}
			$d->close();
		}

		return $result;
	}

	final public function getDir()
	{
		$reflector = new \ReflectionClass(get_class($this));
		return dirname($reflector->getFileName());
	}

	final public function getName()
	{
		if (empty($this->name)) {
			$this->name = str_replace('\\', '', $this->getNamespace());
			$this->name = preg_replace('/Bundle$/', '', $this->name);
		}
		return $this->name;
	}

	final public function getNamespace()
	{
		if (empty($this->namespace)) {
			$this->namespace = substr(get_class($this), 0, strrpos(get_class($this), '\\'));
		}
		return $this->namespace;
	}

	public function getRoutes()
	{
		return [];
	}

	protected function getServices()
	{
		return [];
	}

	protected function getManagers()
	{
		return [];
	}

	protected function getConfig()
	{
		return [];
	}

	private function loadConfig()
	{
		foreach ($this->getConfig() as $key => $value) {
			$this->app->getConfigService()->set($key, $value);
		}
	}

	private function loadServices()
	{
		foreach ($this->getServices() as $class) {
			$this->app[$class] = function () use ($class) {
				return new $class($this->app);
			};
			$this->services[] = $class;
		}
	}

	private function loadManagers()
	{
		foreach ($this->getManagers() as $class) {
			$this->app[$class] = function () use ($class) {
				return new $class($this->app);
			};
			$this->managers[] = $class;
		}
	}

}