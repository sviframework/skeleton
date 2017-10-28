<?php

namespace Svi\Service;

use Svi\Application;

class SessionService
{
	/**
	 * @var Application
	 */
	private $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
		session_start();
	}

	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	public function uns($key)
	{
		unset($_SESSION[$key]);
	}

	public function get($key)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

} 