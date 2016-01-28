<?php

namespace Svi;

class RoutesListCommand extends ConsoleCommand
{

	public function getName()
	{
		return 'routes:list';
	}

	public function getDescription()
	{
		return 'Prints asc list of routes';
	}

	public function execute(array $args)
	{
		var_dump($this->getApp()->getRouting()->getAllRoutes());
	}

}