<?php

namespace Svi;

class Container
{
	private static $instances = array();

	/**
	 * @var Application
	 */
	private $app;

	private $silex;

	private function __construct(Application $app)
	{
		$this->app = $app;
		$this->silex = $app->getSilex();
	}

	private function __clone(){}
	private function __wakeup(){}

	public static function getInstance(Application $app) {
		if (!@self::$instances[get_called_class()]) {
			self::$instances[get_called_class()] = new static($app);
		}
		return self::$instances[get_called_class()];
	}

	/**
	 * @return Application
	 */
	public function getApp()
	{
		return $this->app;
	}

	/**
	 * @return \Silex\Application
	 */
	public function getSilex()
	{
		return $this->silex;
	}

	/**
	 * @param string $schema
	 * @return mixed
	 */
	public function getDb($schema = 'default')
	{
		return $this->silex['dbs'][$schema];
	}

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->app->getConfig();
	}

	/**
	 * @return Routing
	 */
	public function getRouting()
	{
		return $this->app->getRouting();
	}

	/**
	 * @return Session
	 */
	public function getSession()
	{
		return $this->app->getSession();
	}

	/**
	 * @return Cookies
	 */
	public function getCookies()
	{
		return $this->app->getCookies();
	}

	public function getRequest()
	{
		return $this->getApp()->getRequest();
	}

} 