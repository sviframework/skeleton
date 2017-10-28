<?php

namespace Svi\Service;

use Svi\Application;

class ConfigService
{
	private $app;
	protected $config;

	public function __construct(Application $app, $config = null)
	{
		$this->app = $app;
		$this->config = $config ? $config : include($app->getRootDir() . '/app/config/config.php');
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
				return isset($config[$n]) ? $config[$n] : null;
			} elseif (isset($config[$n])) {
				$config = &$config[$n];
			} else {
				break;
			}
		}

		return null;
	}

} 