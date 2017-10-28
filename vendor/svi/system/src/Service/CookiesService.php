<?php

namespace Svi\Service;

use Svi\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CookiesService
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
		$response = new Response();
		$response->headers->setCookie(new Cookie($name, $value, $lifeTime ? time() + $lifeTime : 0));
		$response->sendHeaders();
	}

	public function remove($name) {
		$response = new Response();
		$response->headers->clearCookie($name);
		$response->sendHeaders();
	}

} 