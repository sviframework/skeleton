<?php

namespace Svi;

class Config
{
	private $app;
	protected $config;

	public function __construct(Application $app, $dir = '/app/config/config.php')
	{
		$this->app = $app;
		$this->config = include($app->getRootDir() . $dir);
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