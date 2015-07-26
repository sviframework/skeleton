<?php

namespace Svi;

class Session
{
	private static $_instance;
	/**
	 * @var Application
	 */
	private $app;

	private function __construct(Application $app)
	{
		$this->app = $app;
		session_start();
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
		return @$_SESSION[$key];
	}

} 