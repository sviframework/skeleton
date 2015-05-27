<?php

namespace Svi;

class Cookies
{
	private static $_instance;
	/**
	 * @var Application
	 */
	private $app;

	private function __construct(Application $app)
	{
		$this->app = $app;
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

	function get($key)
	{
		return $this->app->getRequest()->cookies->get($key);
	}

	function has($key)
	{
		return $this->app->getRequest()->cookies->has($key);
	}

	public function set($name, $value, $lifeTime = 0)
	{
		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie($name, $value, $lifeTime ? time() + $lifeTime : 0));
		$response->sendHeaders();
	}

	public function remove($name) {
		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->headers->clearCookie($name);
		$response->sendHeaders();
	}

} 