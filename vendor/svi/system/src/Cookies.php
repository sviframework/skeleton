<?php

namespace Svi;

class Cookies
{
	/**
	 * @var Application
	 */
	private $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
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