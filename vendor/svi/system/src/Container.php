<?php

namespace Svi;

use Doctrine\DBAL\Connection;
use Svi\Service\ConfigService;
use Svi\Service\CookiesService;
use Svi\Service\RoutingService;
use Svi\Service\SessionService;

class Container
{
	private static $instances = array();

	/**
	 * @var Application
	 */
	private $app;

	private function __construct(Application $app)
	{
		$this->app = $app;
	}

	private function __clone(){}
	private function __wakeup(){}

	public static function getInstance(Application $app) {
		if (!array_key_exists($app->getInstanceId(), self::$instances)) {
			self::$instances[$app->getInstanceId()] = [];
		}
		if (!array_key_exists(get_called_class(), self::$instances[$app->getInstanceId()])) {
			self::$instances[$app->getInstanceId()][get_called_class()] = new static($app);
		}

		return self::$instances[$app->getInstanceId()][get_called_class()];
	}

	/**
	 * @return Application
	 */
	public function getApp()
	{
		return $this->app;
	}

	/**
	 * @param string $schema
	 * @return Connection
	 */
	public function getDb($schema = 'default')
	{
		return $this->app['dbs'][$schema];
	}

	/**
	 * @return ConfigService
	 */
	public function getConfigService()
	{
		return $this->app->getConfigService();
	}

	/**
	 * @return RoutingService
	 */
	public function getRoutingService()
	{
		return $this->app->getRoutingService();
	}

	/**
	 * @return SessionService
	 */
	public function getSessionService()
	{
		return $this->app->getSessionService();
	}

	/**
	 * @return CookiesService
	 */
	public function getCookiesService()
	{
		return $this->app->getCookiesService();
	}

	public function getRequest()
	{
		return $this->getApp()->getRequest();
	}

} 