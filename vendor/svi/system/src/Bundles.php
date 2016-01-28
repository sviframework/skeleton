<?php

namespace Svi;

class Bundles
{
	private static $_instance;
	private $app;
	private $bundles;

	private function __construct(Application $app)
	{
		$this->app = $app;
		foreach ($app->getConfig()->get('bundles') as $bundleName) {
			$this->bundles[] = new $bundleName($app);
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

	/**
	 * @return Bundle[]
	 */
	public function getBundles()
	{
		return $this->bundles;
	}

	public function getEntityClasses()
	{
		$result = [];
		/** @var Bundle $b */
		foreach ($this->bundles as $b) {
			$result = array_merge($result, $b->getEntityClasses());
		}

		return $result;
	}

	public function getCommandClasses()
	{
		$result = [];
		/** @var Bundle $b */
		foreach ($this->bundles as $b) {
			$result = array_merge($result, $b->getCommandClasses());
		}

		return $result;
	}

} 