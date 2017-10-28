<?php

namespace Svi\Service\ConsoleService;

use Svi\Application;

abstract class ConsoleCommand
{
	private $app;

	function __construct(Application $app)
	{
		$this->app = $app;
	}

	abstract public function getName();

	abstract public function getDescription();

	abstract public function execute(array $args);

	/**
	 * @return Application
	 */
	protected function getApp()
	{
		return $this->app;
	}

	protected function write($text)
	{
		print $text;
	}

	protected function writeLn($text = '')
	{
		print $text . "\n    ";
	}

} 