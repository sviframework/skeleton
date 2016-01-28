<?php

namespace Svi;

class Console
{
	private static $_instance;
	/**
	 * @var Application
	 */
	private $app;
	private $commands;
	private $argv;

	private function __construct(Application $app, $argv)
	{
		$this->app = $app;
		$this->argv = $argv;

		$this->addCommand(new EntityUpdateCommand($this->app));
		$this->addCommand(new AssetsInstallCommand($this->app));
		$this->addCommand(new RoutesListCommand($this->app));
		foreach ($this->app->getBundles()->getCommandClasses() as $c) {
			$this->addCommand(new $c($this->app));
		}
	}

	private function __clone() {}
	private function __wakeup(){}

	public static function getInstance(Application $app, $argv)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($app, $argv);
		}

		return self::$_instance;
	}

	public function run()
	{
		$argv = $this->argv;

		$this->writeLn();
		array_shift($argv);
		if (count($argv) > 0) {
			$command = array_shift($argv);

			if (!isset($this->commands[$command])) {
				$this->writeLn('There is no command "' . $command . '". Available commands:');
				$this->writeLn();
				$this->listAllCommands();
			} else {
				$command = $this->commands[$command];
				$command->execute($argv);
			}
		} else {
			$this->listAllCommands();
		}
		print "\n";
	}

	public function addCommand(ConsoleCommand $command)
	{
		$this->commands[$command->getName()] = $command;
	}

	protected function listAllCommands()
	{
		foreach ($this->commands as $key => $c) {
			$this->writeLn($key . ' - ' . $c->getDescription());
		}
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