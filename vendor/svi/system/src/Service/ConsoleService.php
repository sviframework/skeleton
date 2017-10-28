<?php

namespace Svi\Service;

use Svi\Application;
use Svi\Service\ConsoleService\ConsoleCommand;

class ConsoleService
{
	/** @var Application */
	private $app;
	/** @var ConsoleCommand[] */
	private $commands;
	private $argv;

	public function __construct(Application $app, $argv)
	{
		$this->app = $app;
		$this->argv = $argv;

		foreach ($this->app->getBundlesService()->getCommandClasses() as $c) {
			$this->addCommand(new $c($this->app));
		}
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