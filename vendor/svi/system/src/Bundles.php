<?php

namespace Svi;

class Bundles
{
	private $app;
	private $bundles;

	public function __construct(Application $app)
	{
		$this->app = $app;
		foreach ($app->getConfig()->get('bundles') as $bundleName) {
			/** @var Bundle $bundle */
			$bundle = new $bundleName($app);
			$this->bundles[] = $bundle;
			$app[$bundleName] = $bundle;
		}
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

	/**
	 * @return Manager[]
	 */
	public function getManagerInstances()
	{
		$result = [];
		/** @var Bundle $b */
		foreach ($this->bundles as $b) {
			$result = array_merge($result, $b->getManagerInstances());
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