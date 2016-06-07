<?php

namespace Svi;

abstract class Bundle
{
	private $name;
	private $namespace;
	private $app;

	function __construct(Application $app)
	{
		$this->app = $app;
		$this->loadConfig();
		$this->loadServices();
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

		return array();
	}

	public function getEntityClasses()
	{
		$result = array();
		$dir = $this->getDir() . '/Entity';
		if (file_exists($dir)) {
			$d = dir($dir);
			while (false !== ($entry = $d->read())) {
				if (preg_match('/^(.*)\.php$/', $entry, $matches)) {
					$result[] = $this->getNamespace() . '\\Entity\\' . $matches[1];
				}
			}
			$d->close();
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
		return array();
	}

	protected function getServices()
	{
		return array();
	}

	protected function getConfig()
	{
		return array();
	}

	private function loadConfig()
	{
		foreach ($this->getConfig() as $key => $value) {
			$this->app->getConfig()->set($key, $value);
		}
	}

	private function loadServices()
	{
		$app = $this->app;
		foreach ($this->getServices() as $name => $class) {
			$className = $this->getNamespace().'\\'.$class;
			$app->getSilex()[$name] = function() use ($className, $app) {
				return new $className($app);
			};
		}
	}

} 