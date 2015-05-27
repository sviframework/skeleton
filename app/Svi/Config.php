<?php

namespace Svi;

class Config
{
	private static $_instance;
	protected $config;

	private function __construct()
	{
		$this->config = include(__DIR__ . '/./../config/config.php');
	}

	private function __clone() {}
	private function __wakeup(){}

	public static function getInstance()
	{
		if (self::$_instance === null) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function set($key, $value)
	{
		$this->config[$key] = $value;
	}

	public function getParameter($key)
	{
		return $this->get('parameters.' . $key);
	}

	public function get($name)
	{
		$name = explode('.', $name);
		$config = &$this->config;
		foreach ($name as $key => &$n) {
			if ($key >= count($name) - 1) {
				return @$config[$n];
			} elseif (isset($config[$n])) {
				$config = &$config[$n];
			} else {
				break;
			}
		}

		return null;
	}

} 