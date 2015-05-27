<?php

namespace Svi;

class Bundles
{
	private static $_instance;
	private $app;
	private $bundles;

	private function __construct(Application $app)
	{
		require_once __DIR__ . '/./Container.php';
		require_once __DIR__.'/./ContainerAware.php';
		require_once __DIR__.'/./Bundle.php';

		$this->app = $app;
		foreach ($app->getConfig()->get('bundles') as $vendor => $bundles) {
			foreach ($bundles as $bundle) {
				$className = '\\' . $vendor . '\\' . $bundle . 'Bundle\\' . 'Bundle';
				$this->bundles[] = new $className($app);
			}
		}
	}

	private function __clone() {}
	private function __wakeup(){}

	public static function getInstance(Application $app)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($app);
		}

		return self::$_instance;
	}

	public function getBundles()
	{
		return $this->bundles;
	}

	public function getEntityClasses()
	{
		$result = [];
		foreach ($this->bundles as $b) {
			$result = array_merge($result, $b->getEntityClasses());
		}

		return $result;
	}

	public function getCommandClasses()
	{
		$result = [];
		foreach ($this->bundles as $b) {
			$result = array_merge($result, $b->getCommandClasses());
		}

		return $result;
	}

} 